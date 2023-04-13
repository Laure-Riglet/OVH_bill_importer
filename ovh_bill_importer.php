<?php

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/Client.php';
require __DIR__ . '/app/InvoiceController.php';

$requestedServices = ['OVH', 'KIM', 'SYS'];

foreach ($requestedServices as $requestedService) {

    // Get the client
    $requestedServiceShortName = $requestedService;
    $requestedServiceLongName = Invoice::getServiceLongName($requestedServiceShortName);

    $client = (new Client($requestedServiceShortName))->getClient();

    // Retrieve all bills IDs
    $billsOriginalIds = InvoiceController::getAllBillsIds($client);

    // Test client connection
    try {
        $client->get('/me/bill/' . $billsOriginalIds[0]);
    } catch (GuzzleHttp\Exception\ClientException $e) {
        echo $requestedServiceLongName . ' client connection failed' . PHP_EOL;
        continue;
    }

    foreach ($billsOriginalIds as $billOriginalId) {

        // Check if the bill already exists in the database
        if (Invoice::exists($billOriginalId)) {
            continue;
        }

        // Get the bill's data
        $bill = InvoiceController::getBill($client, $billOriginalId);

        // Check if bill is new, aka has been issued in the current month
        $issueDate = new \DateTime($bill['date']);
        $limitDate = new \DateTime('01-' . date('m-Y') . ' 00:00:00');
        if ($issueDate < $limitDate) {
            continue;
        }

        // Check if bill's amount is greater than 0
        if ($bill['priceWithTax']['value'] === 0) {
            continue;
        }

        // Check if bill's original ID doesn't start with 'PP_'
        if (strpos($bill['billId'], 'PP_') === 0) {
            continue;
        }

        // Tell the user that a new bill has been found
        echo 'New bill found for ' . $requestedServiceLongName . ': ' . $bill['billId'] . PHP_EOL;

        // Create the invoice object
        $invoice = new Invoice();
        $invoice->setService($requestedServiceLongName);
        $invoice->setOriginalId($bill['billId']);
        $invoice->setFileName(
            $issueDate->format('Ymd')
                . '-OVH-'
                . $bill['billId']
                . '-'
                . InvoiceController::getFormattedPrice($bill['priceWithTax']['value'])
        );
        $invoice->setFilePath(
            $requestedServiceLongName
                . '/'
                . $issueDate->format('Y')
                . '/'
                . $issueDate->format('m')
                . '/'
        );
        $invoice->setIssuedAt($issueDate->format('Y-m-d H:i:s'));
        $invoice->setPriceWithoutTax($bill['priceWithoutTax']['value']);
        $invoice->setPriceWithTax($bill['priceWithTax']['value']);
        $invoice->setPdfUrl($bill['pdfUrl']);

        // Insert the invoice in the database
        $invoice->insert();

        // Download the invoice
        $pdfFile = file_get_contents($bill['pdfUrl']);

        // Create the directories if they don't exist
        $dirService = __DIR__ . '/invoices/' . $requestedServiceLongName . '/';
        if (!file_exists($dirService)) {
            mkdir($dirService, 0777, true);
        }
        $dirYear = __DIR__ . '/invoices/' . $requestedServiceLongName . '/' . $issueDate->format('Y') . '/';
        if (!file_exists($dirYear)) {
            mkdir($dirYear, 0777, true);
        }
        $dirMonth = __DIR__ . '/invoices/' . $requestedServiceLongName . '/' . $issueDate->format('Y') . '/' . $issueDate->format('m') . '/';
        if (!file_exists($dirMonth)) {
            mkdir($dirMonth, 0777, true);
        }

        // Save the invoice
        file_put_contents(__DIR__ . '/invoices/' . $invoice->getFilePath() . $invoice->getFileName() . '.pdf', $pdfFile);

        // Tell the user that the invoice has been saved
        echo 'Invoice saved: ' . $invoice->getFileName() . PHP_EOL;

        // Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        $emailData = InvoiceController::getEmailData();

        try {
            // Server settings
            $mail->SMTPDebug = 0;                                       //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $emailData['host'];                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $emailData['username'];                 //SMTP username
            $mail->Password   = $emailData['password'];                 //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $emailData['port'];                     //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            // Recipients
            $mail->setFrom($emailData['sender']);

            foreach ($emailData['to'] as $to) {
                $mail->addAddress($to);
            }

            foreach ($emailData['cc'] as $cc) {
                if (empty($cc)) {
                    continue;
                }
                $mail->addCC($cc);
            }

            foreach ($emailData['bcc'] as $bcc) {
                if (empty($bcc)) {
                    continue;
                }
                $mail->addBCC($bcc);
            }

            // Attachments
            $mail->addAttachment(
                __DIR__ . '/invoices/' . $invoice->getFilePath() . $invoice->getFileName() . '.pdf',
                $invoice->getFileName() . '.pdf'
            );

            // Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'New invoice ' . $invoice->getService() . ' ' . $invoice->getOriginalId();
            $mail->Body    = '<p>New invoice received: <b>' . $invoice->getService() . ' ' . $invoice->getOriginalId() . '</b> for ' . $invoice->getPriceWithTax() . '€, issued on ' . $issueDate->format('Y-m-d H:i:s') . '.</p>';
            $mail->AltBody = 'New invoice received: ' . $invoice->getService() . ' ' . $invoice->getOriginalId() . ' for ' . $invoice->getPriceWithTax() . '€, issued on ' . $issueDate->format('Y-m-d H:i:s') . '.';

            // Send the email and tell the user if it was successful or not
            $mail->send();
            echo 'Message has been sent' . PHP_EOL;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}" . PHP_EOL;
        }
    }
}
