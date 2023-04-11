<?php

require __DIR__ . '/../vendor/autoload.php';

use \Ovh\Api;

$configData = parse_ini_file(__DIR__ . '/../config.ini');

$applicationKey = $configData['OVH_APPLICATION_KEY'];
$applicationSecret = $configData['OVH_APPLICATION_SECRET'];
$endpoint = $configData['OVH_ENDPOINT'];
$consumerKey = $configData['OVH_CONSUMER_KEY'];

$ovh = new Api(
    $applicationKey,
    $applicationSecret,
    $endpoint,
    $consumerKey
);

$bills = $ovh->get('/me/bill');

foreach ($bills as $bill) {
    $billDetails = $ovh->get('/me/bill/' . $bill);
    echo $billDetails['billId'] . ' - ' . $billDetails['priceWithoutTax'] . ' - ' . $billDetails['priceWithTax'] . ' - ' . $billDetails['date'] . PHP_EOL;
};