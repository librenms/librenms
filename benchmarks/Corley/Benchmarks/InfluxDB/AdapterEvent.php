<?php
namespace Corley\Benchmarks\InfluxDB;

use InfluxDB\Client;
use InfluxDB\Adapter\GuzzleAdapter;
use InfluxDB\Adapter\UdpAdapter;
use Athletic\AthleticEvent;
use GuzzleHttp\Client as HttpClient;
use InfluxDB\Options;

class AdapterEvent extends AthleticEvent
{
    private $httpClient;
    private $udpClient;

    public function setUp()
    {
        $client = new Client();
        $options = new Options();
        $options->setUsername("root");
        $options->setPassword("root");
        $options->setDatabase("bench");
        $client->setAdapter(
            new GuzzleAdapter(new HttpClient(), $options)
        );
        $this->httpClient = $client;

        $client = new Client();
        $client->setAdapter(new UdpAdapter(new Options()));
        $this->udpClient = $client;
    }

    /**
     * @iterations 1000
     */
    public function sendDataUsingHttpAdapter()
    {
        $this->httpClient->mark("metric.name", ["key" => "value"]);
    }

    /**
     * @iterations 1000
     */
    public function sendDataUsingUdpAdapter()
    {
        $this->udpClient->mark("metric.name", ["key" => "value"]);
    }
}
