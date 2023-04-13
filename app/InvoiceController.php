<?php

require __DIR__ . '/../app/Invoice.php';

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
     * @param $originalId   string  The bill ID
     * @return array    An array of bill data
     */
    static function getBill($client, $originalId)
    {
        try {
            return $client->get('/me/bill/' . $originalId);
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
            'host' => $configData['EMAIL_HOST'],
            'port' => $configData['EMAIL_PORT'],
            'username' => $configData['EMAIL_USERNAME'],
            'password' => $configData['EMAIL_PASSWORD'],
            'sender' => $configData['EMAIL_FROM'],
            'to' => explode(',', $configData['EMAIL_TO']),
            'cc' => isset($configData['EMAIL_CC']) ? explode(',', $configData['EMAIL_CC']) : [],
            'bcc' => isset($configData['EMAIL_BCC']) ? explode(',', $configData['EMAIL_BCC']) : []
        ];
    }
}
