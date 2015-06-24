<?php
namespace InfluxDB\Adapter;

use GuzzleHttp\Client;
use InfluxDB\Options;

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
        return $this->getHttpEndpoint("write");
    }

    protected function getHttpQueryEndpoint()
    {
        return $this->getHttpEndpoint("query");
    }

    private function getHttpEndpoint($operation)
    {
        $url = sprintf(
            "%s://%s:%d%s/%s",
            $this->getOptions()->getProtocol(),
            $this->getOptions()->getHost(),
            $this->getOptions()->getPort(),
            $this->getOptions()->getPrefix(),
            $operation
        );

        return $url;
    }
}
