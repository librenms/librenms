<?php
namespace InfluxDB\Adapter;

use GuzzleHttp\Client;
use InfluxDB\Options;

/**
 * Class GuzzleAdapter
 * @package InfluxDB\Adapter
 *
 * @deprecated
 */
class GuzzleAdapter implements AdapterInterface, QueryableInterface
{
    private $httpClient;
    private $options;

    public function __construct(Client $httpClient, Options $options)
    {
        $this->httpClient = $httpClient;
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function send($message, $timePrecision = false)
    {
        $httpMessage = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
            "body" => json_encode($message)
        ];

        if ($timePrecision) {
            $httpMessage["query"]["time_precision"] = $timePrecision;
        }

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

    public function getDatabases()
    {
        $options = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
        ];

        $endpoint = $this->options->getHttpDatabaseEndpoint();

        return $this->httpClient->get($endpoint, $options)->json();
    }

    public function createDatabase($name)
    {
        $httpMessage = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
            "body" => json_encode(["name" => $name])
        ];

        $endpoint = $this->options->getHttpDatabaseEndpoint();
        return $this->httpClient->post($endpoint, $httpMessage)->json();
    }

    public function deleteDatabase($name)
    {
        $httpMessage = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
        ];

        $endpoint = $this->options->getHttpDatabaseEndpoint($name);
        return $this->httpClient->delete($endpoint, $httpMessage)->json();
    }
}
