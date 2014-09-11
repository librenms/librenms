<?php

namespace InfluxDB\Adapter;

use GuzzleHttp\Client;
use InfluxDB\Options;

class GuzzleAdapter implements AdapterInterface
{
    private $httpClient;
    private $options;
    private $database;

    public function __construct(Client $httpClient, Options $options)
    {
        $this->httpClient = $httpClient;
        $this->options = $options;
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    public function send($message)
    {
        $httpMessage = [
            "body" => json_encode($message)
        ];
        $endpoint = $this->options->getTcpEndpointFor($this->getDatabase());

        $this->httpClient->post($endpoint, $httpMessage);
    }
}
