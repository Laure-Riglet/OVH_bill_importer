<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Client.php';
require_once __DIR__ . '/app/InvoiceController.php';
require_once __DIR__ . '/app/Invoice.php';
require_once __DIR__ . '/app/Mail.php';

$requestedServices = ['OVH', 'KIM', 'SYS'];

foreach ($requestedServices as $requestedService) {

    // Get the client
    $requestedServiceShortName = $requestedService;
    $requestedServiceLongName = Invoice::getServiceLongName($requestedServiceShortName);

    $client = (new Client($requestedServiceShortName))->getClient();

    // Retrieve all bills IDs
    $billsOriginal_ids = InvoiceController::getAllBillsIds($client);

    // Test client connection
    try {
        $client->get('/me/bill/' . $billsOriginal_ids[0]);
    } catch (GuzzleHttp\Exception\ClientException $e) {
        echo $requestedServiceLongName . ' client connection failed' . PHP_EOL;
        continue;
    }

    foreach ($billsOriginal_ids as $billOriginal_id) {

        // Check if the bill already exists in the database
        if (Invoice::exists($billOriginal_id)) {
            continue;
        }

        // Get the bill's data
        $bill = InvoiceController::getBill($client, $billOriginal_id);

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
        $invoice->setFilename(
            $issueDate->format('Ymd')
                . '-OVH-'
                . $bill['billId']
                . '-'
                . InvoiceController::getFormattedPrice($bill['priceWithTax']['value'])
        );
        $invoice->setFilepath(
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
        $dirMonth = __DIR__ . '/invoices/' . $requestedServiceLongName . '/' . $issueDate->format('Y') . '/'
            . $issueDate->format('m') . '/';
        if (!file_exists($dirMonth)) {
            mkdir($dirMonth, 0777, true);
        }

        // Save the invoice
        file_put_contents(__DIR__ . '/invoices/' . $invoice->getFilepath()
            . $invoice->getFilename() . '.pdf', $pdfFile);

        // Tell the user that the invoice has been saved
        echo 'Invoice saved: ' . $invoice->getFilename() . PHP_EOL;

        // Send the invoice by email using PHP mail() function
        $emailData = InvoiceController::getEmailData();

        echo Mail::sendMail(
            __DIR__ . '/invoices/' . $invoice->getFilepath() . $invoice->getFilename() . '.pdf',
            $invoice->getFilename(),
            'New invoice received: ' . $invoice->getService() . ' ' . $invoice->getOriginalId() . ' for ' . $invoice->getPriceWithTax() . 'EUR, issued on ' . $invoice->getIssuedAt() . '.',
            'New invoice ' . $invoice->getService() . ' ' . $invoice->getOriginalId(),
            $emailData['to'],
            $emailData['cc'],
            $emailData['bcc'],
            $emailData['sender']
        );
    }
}
