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
        $options = new Options();
        $options->setHost("localhost");
        $options->setPort(8086);
        $options->setUsername("root");
        $options->setPassword("root");
        $options->setDatabase("tcp.test");

        $client = new Client(new GuzzleAdapter(new HttpClient(), $options));

        $this->httpClient = $client;

        $opts = new Options();
        $opts->setPort(4444);

        $client = new Client(new UdpAdapter($opts));

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
