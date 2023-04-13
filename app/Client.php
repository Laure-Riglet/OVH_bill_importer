<?php

use \Ovh\Api;

class Client
{
    private $configData;
    private $applicationKey;
    private $applicationSecret;
    private $endpoint;
    private $consumerKey;

    public function __construct(string $serviceShortName)
    {
        $this->configData = parse_ini_file(__DIR__ . '/../config.ini');
        $this->applicationKey = $this->configData[$serviceShortName . '_APPLICATION_KEY'];
        $this->applicationSecret = $this->configData[$serviceShortName . '_APPLICATION_SECRET'];
        $this->endpoint = $this->configData[$serviceShortName . '_ENDPOINT'];
        $this->consumerKey = $this->configData[$serviceShortName . '_CONSUMER_KEY'];
    }

    public function getClient()
    {
        return new Api(
            $this->applicationKey,
            $this->applicationSecret,
            $this->endpoint,
            $this->consumerKey
        );
    }
}
