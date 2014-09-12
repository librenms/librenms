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

    public function send($message)
    {
        $httpMessage = [
            "body" => json_encode($message)
        ];
        $endpoint = $this->options->getTcpEndpoint();

        $this->httpClient->post($endpoint, $httpMessage);
    }
}
