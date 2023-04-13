<?php

require __DIR__ . '/../vendor/autoload.php';
require 'Client.php';
require 'InvoiceController.php';

// Get the client
$requestedServiceShortName = 'L4U';
$client = (new Client($requestedServiceShortName))->getClient();

// Retrieve all bills IDs
$billsOriginalIds = InvoiceController::getAllBillsIds($client);

// filename format: nom_du_service/annee/mois/Date-OVH-Numero-Montant.pdf

foreach ($billsOriginalIds as $billOriginalId) {

    // Check if the bill already exists in the database
    if (Invoice::exists($billOriginalId)) {
        continue;
    }

    // Get the bill's data
    $bill = InvoiceController::getBill($client, $billOriginalId);

    // Check if bill is new aka issued in the last month
    $issueDate = new \DateTime($bill['date']);
    // $limitDate = date_create()->modify('-1 month');
    // if ($issueDate < $limitDate) {
    //     continue;
    // }

    // Check if bill's amount is greater than 0
    if ($bill['priceWithTax']['value'] === 0) {
        continue;
    }

    // Check if bill's original ID doesn't start with 'PP_'
    if (strpos($bill['billId'], 'PP_') === 0) {
        continue;
    }

    // Create the invoice object
    $invoice = new Invoice();
    $invoice->setService(Invoice::getServiceByShortName($requestedServiceShortName));
    $invoice->setOriginalId($bill['billId']);
    $invoice->setFileName(
        $issueDate->format('Ymd')
            . '-OVH-'
            . $bill['billId']
            . '-'
            . InvoiceController::getFormattedPrice($bill['priceWithTax']['value'])
    );
    $invoice->setFilePath(
        'OVH/'
            . $issueDate->format('Y')
            . '/'
            . $issueDate->format('m')
            . '/'
            . $invoice->getFileName()
            . '.pdf'
    );
    $invoice->setIssuedAt($issueDate->format('Y-m-d H:i:s'));
    $invoice->setPriceWithoutTax($bill['priceWithoutTax']['value']);
    $invoice->setPriceWithTax($bill['priceWithTax']['value']);
    $invoice->setPdfUrl($bill['pdfUrl']);

    // Insert the invoice in the database
    $invoice->insert();
}
