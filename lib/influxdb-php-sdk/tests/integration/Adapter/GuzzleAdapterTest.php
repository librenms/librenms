<?php
namespace InfluxDB\Integration\Adapter;

use DateTime;
use DateTimeZone;
use InfluxDB\Options;
use InfluxDB\Client;
use InfluxDB\Adapter\GuzzleAdapter;
use GuzzleHttp\Client as GuzzleHttpClient;
use InfluxDB\Integration\Framework\TestCase as InfluxDBTestCase;

class GuzzleAdapterTest extends InfluxDBTestCase
{
    public function testAdapterWriteDataCorrectly()
    {
        $this->getClient()->createDatabase("tcp.test");

        $options = new Options();
        $options->setPort(8086);
        $options->setDatabase("tcp.test");

        $http = new GuzzleHttpClient();
        $adapter = new GuzzleAdapter($http, $options);

        $adapter->send([
            "points" => [
                [
                    "measurement" => "vm-serie",
                    "fields" => [
                        "cpu" => 18.12,
                        "free" => 712423,
                    ],
                ],
            ]
        ]);

        $this->assertSerieExists("tcp.test", "vm-serie");
        $this->assertSerieCount("tcp.test", "vm-serie", 1);
        $this->assertValueExistsInSerie("tcp.test", "vm-serie", "cpu", 18.12);
        $this->assertValueExistsInSerie("tcp.test", "vm-serie", "free", 712423);
    }

    public function testWorksWithProxies()
    {
        $this->getClient()->createDatabase("proxy.test");

        $options = new Options();
        $options->setPort(9000);
        $options->setDatabase("proxy.test");
        $options->setPrefix("/influxdb");

        $http = new GuzzleHttpClient();
        $adapter = new GuzzleAdapter($http, $options);

        $adapter->send([
            "points" => [
                [
                    "measurement" => "vm-serie",
                    "fields" => [
                        "cpu" => 18.12,
                        "free" => 712423,
                    ],
                ],
            ]
        ]);

        $this->assertSerieExists("proxy.test", "vm-serie");
        $this->assertSerieCount("proxy.test", "vm-serie", 1);
        $this->assertValueExistsInSerie("proxy.test", "vm-serie", "cpu", 18.12);
        $this->assertValueExistsInSerie("proxy.test", "vm-serie", "free", 712423);
    }
}
