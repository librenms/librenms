<?php

namespace InfluxDB\Adapter;

use GuzzleHttp\Client;
use InfluxDB\Options;

class GuzzleAdapter implements AdapterInterface, QueryableInterface
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
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
            "body" => json_encode($message)
        ];

        $endpoint = $this->options->getHttpSeriesEndpoint();
        return $this->httpClient->post($endpoint, $httpMessage);
    }

    public function query($query, $timePrecision = false)
    {
        $options = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
            'query' => [
                "q" => $query,
            ]
        ];

        if ($timePrecision) {
            $options["query"]["time_precision"] = $timePrecision;
        }

        $endpoint = $this->options->getHttpSeriesEndpoint();
        return $this->httpClient->get($endpoint, $options)->json();
    }
}
