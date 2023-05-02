<?php

require_once __DIR__ . '/../app/Invoice.php';

class InvoiceController
{
    /**
     * Get all issued bills IDs
     * @param $service  string  The service name
     * @return array    An array of bill IDs
     */
    static function getAllBillsIds($client)
    {
        try {
            $bills = $client->get('/me/bill');
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            return $responseBodyAsString;
        }
        return $bills;
    }

    /**
     * Get a bill's data
     * @param $original_id   string  The bill ID
     * @return array    An array of bill data
     */
    static function getBill($client, $original_id)
    {
        try {
            return $client->get('/me/bill/' . $original_id);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            echo $responseBodyAsString . PHP_EOL;
        }
    }

    /** 
     * Get a formatted price field for the filename
     * @param $price    string  The price
     * @return string   The formatted price
     */
    static function getFormattedPrice(float $price)
    {
        $price = strval($price);
        $dotPos = strpos($price, '.');
        $priceInt = substr($price, 0, $dotPos - strlen($price));
        $priceDec = strlen(substr($price, $dotPos + 1)) == 1 ? substr($price, $dotPos + 1) . '0' : substr($price, $dotPos + 1);

        return $priceInt . 'EUR' . $priceDec;
    }

    /**
     * Get emails of invoice recipients
     * @return array    An array of emails
     */
    static function getEmailData()
    {
        $configData = parse_ini_file(__DIR__ . '/../config.ini');
        return [
            'sender' => $configData['EMAIL_FROM'],
            'to' => $configData['EMAIL_TO'],
            'cc' => isset($configData['EMAIL_CC']) ? $configData['EMAIL_CC'] : "",
            'bcc' => $configData['EMAIL_BCC'] ? $configData['EMAIL_BCC'] : ""
        ];
    }
}
