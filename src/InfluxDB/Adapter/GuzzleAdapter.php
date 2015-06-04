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
                "db" => $this->getOptions()->getDatabase(),
            ]
        ];

        if ($timePrecision) {
            $options["query"]["time_precision"] = $timePrecision;
        }

        $endpoint = $this->options->getHttpQueryEndpoint();

        return $this->httpClient->get($endpoint, $options)->json();
    }

    /**
     * {@inheritDoc}
     */
    public function getDatabases()
    {
        $options = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
            "query" => [
                "q" => "show databases",
            ],
        ];

        $endpoint = $this->options->getHttpQueryEndpoint();
        return $this->httpClient->get($endpoint, $options)->json();
    }

    /**
     * {@inheritDoc}
     */
    public function createDatabase($name)
    {
        $httpMessage = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
            "query" => ["q" => "CREATE DATABASE \"{$name}\""],
        ];

        $endpoint = $this->options->getHttpQueryEndpoint();
        return $this->httpClient->get($endpoint, $httpMessage)->json();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteDatabase($name)
    {
        $httpMessage = [
            "auth" => [$this->options->getUsername(), $this->options->getPassword()],
            "query" => ["q" => "drop database \"{$name}\""],
        ];

        $endpoint = $this->options->getHttpQueryEndpoint();
        return $this->httpClient->get($endpoint, $httpMessage)->json();
    }
}
