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
    /**
     * @var GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * @var \InfluxDB\Options
     */
    private $options;

    /**
     * @param Client $httpClient
     * @param Options $options
     */
    public function __construct(Client $httpClient, Options $options)
    {
        $this->httpClient = $httpClient;
        $this->options = $options;
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
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

    /**
     * {@inheritDoc}
     */
    public function getDatabases()
    {
        $options = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
        ];

        $endpoint = $this->options->getHttpDatabaseEndpoint();

        return $this->httpClient->get($endpoint, $options)->json();
    }

    /**
     * {@inheritDoc}
     */
    public function createDatabase($name)
    {
        $httpMessage = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
            "body" => json_encode(["name" => $name])
        ];

        $endpoint = $this->options->getHttpDatabaseEndpoint();
        return $this->httpClient->post($endpoint, $httpMessage)->json();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteDatabase($name)
    {
        $httpMessage = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
        ];

        $endpoint = $this->options->getHttpDatabaseEndpoint($name);
        return $this->httpClient->delete($endpoint, $httpMessage)->json();
    }
}
