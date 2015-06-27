<?php
namespace InfluxDB\Integration\Adapter;

use InfluxDB\Integration\Framework\TestCase as InfluxDBTestCase;
use InfluxDB\Adapter\UdpAdapter;
use InfluxDB\Options;

class UdpAdapterTest extends InfluxDBTestCase
{
    public function testWriteSimplePointsUsingDirectWrite()
    {
        $options = (new Options())
            ->setPort(4444);
        $adapter = new UdpAdapter($options);

        $this->getClient()->createDatabase("udp.test");

        $adapter->write("cpu value=12.33 " . (int)(microtime(true)*1e9));

        sleep(2);

        $this->assertSerieExists("udp.test", "cpu");
        $this->assertSerieCount("udp.test", "cpu", 1);
        $this->assertValueExistsInSerie("udp.test", "cpu", "value", 12.33);
    }

    public function testWriteSimplePointsUsingSendMethod()
    {
        $options = (new Options())
            ->setPort(4444);
        $adapter = new UdpAdapter($options);

        $this->getClient()->createDatabase("udp.test");

        $adapter->send([
            "retentionPolicy" => "default",
            "points" => [
                [
                    "measurement" => "mem",
                    "fields" => [
                        "value" => 1233,
                        "with_string" => "this is a string",
                    ],
                ],
            ],
        ]);

        sleep(2);

        $this->assertSerieExists("udp.test", "mem");
        $this->assertSerieCount("udp.test", "mem", 1);
        $this->assertValueExistsInSerie("udp.test", "mem", "value", 1233);
        $this->assertValueExistsInSerie("udp.test", "mem", "with_string", "this is a string");
    }
}
