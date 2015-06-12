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
class GuzzleAdapter extends AdapterAbstract implements QueryableInterface
{
    private $httpClient;

    public function __construct(Client $httpClient, Options $options)
    {
        parent::__construct($options);

        $this->httpClient = $httpClient;
    }

    public function send(array $message)
    {
        $message = array_replace_recursive($this->getMessageDefaults(), $message);

        if (!count($message["tags"])) {
            unset($message["tags"]);
        }

        $httpMessage = [
            "auth" => [$this->getOptions()->getUsername(), $this->getOptions()->getPassword()],
            "body" => json_encode($message)
        ];

        $endpoint = $this->getHttpSeriesEndpoint();
        return $this->httpClient->post($endpoint, $httpMessage);
    }

    public function query($query)
    {
        $options = [
            "auth" => [$this->getOptions()->getUsername(), $this->getOptions()->getPassword()],
            'query' => [
                "q" => $query,
                "db" => $this->getOptions()->getDatabase(),
            ]
        ];

        return $this->get($options);
    }

    private function get(array $httpMessage)
    {
        $endpoint = $this->getHttpQueryEndpoint();
        return $this->httpClient->get($endpoint, $httpMessage)->json();
    }

    protected function getHttpSeriesEndpoint()
    {
        return sprintf(
            "%s://%s:%d/write",
            $this->getOptions()->getProtocol(),
            $this->getOptions()->getHost(),
            $this->getOptions()->getPort()
        );
    }

    protected function getHttpQueryEndpoint($name = false)
    {
        $url = sprintf(
            "%s://%s:%d/query",
            $this->getOptions()->getProtocol(),
            $this->getOptions()->getHost(),
            $this->getOptions()->getPort()
        );

        return $url;
    }
}
