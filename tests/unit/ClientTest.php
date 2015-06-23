<?php
namespace InfluxDB;

use DateTime;
use DateTimeZone;
use InfluxDB\Adapter\GuzzleAdapter as InfluxHttpAdapter;
use InfluxDB\Options;
use InfluxDB\Adapter\UdpAdapter;
use GuzzleHttp\Client as GuzzleHttpClient;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testMarkNewMeasurementWithShortSyntax()
    {
        $mock = $this->prophesize("InfluxDB\\Adapter\\WritableInterface");
        $mock->send([
            "points" => [
                [
                    "measurement" => "tcp.test",
                    "fields" => [
                        "mark" => "element"
                    ]
                ]
            ]
        ])->shouldBeCalledTimes(1);

        $object = new Client($mock->reveal());
        $object->mark("tcp.test", ["mark" => "element"]);
    }

    public function testWriteDirectMessages()
    {
        $mock = $this->prophesize("InfluxDB\\Adapter\\WritableInterface");
        $mock->send([
            "tags" => [
                "dc" => "eu-west-1",
            ],
            "points" => [
                [
                    "measurement" => "vm-serie",
                    "fields" => [
                        "cpu" => 18.12,
                        "free" => 712423,
                    ]
                ]
            ]
        ])->shouldBeCalledTimes(1);
        $object = new Client($mock->reveal());

        $object->mark([
            "tags" => [
                "dc"  => "eu-west-1",
            ],
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
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testNeedWritableInterfaceDuringMark()
    {
        $client = new Client(new \stdClass());
        $client->mark("OK", []);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testNeedQueryableInterfaceDuringQuery()
    {
        $client = new Client(new \stdClass());
        $client->query("OK", []);
    }
}
