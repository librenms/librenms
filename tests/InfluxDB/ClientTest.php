<?php
namespace InfluxDB;

use InfluxDB\Adapter\GuzzleAdapter as InfluxHttpAdapter;
use InfluxDB\Options;
use InfluxDB\Adapter\UdpAdapter;
use GuzzleHttp\Client as GuzzleHttpClient;
use InfluxDB\Filter\ColumnsPointsFilter;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $rawOptions;
    private $object;
    private $options;

    private $anotherClient;

    public function setUp()
    {
        $options = include __DIR__ . '/../bootstrap.php';
        $this->rawOptions = $options;

        $tcpOptions = $options["tcp"];

        $options = new Options();
        $options->setHost($tcpOptions["host"]);
        $options->setPort($tcpOptions["port"]);
        $options->setUsername($tcpOptions["username"]);
        $options->setPassword($tcpOptions["password"]);
        $options->setDatabase($tcpOptions["database"]);

        $this->options = $options;

        $guzzleHttp = new GuzzleHttpClient();
        $adapter = new InfluxHttpAdapter($guzzleHttp, $options);

        $influx = new Client();
        $influx->setAdapter($adapter);
        $this->object = $influx;

        $databases = $this->object->getDatabases();
        foreach ($databases as $database) {
            $this->object->deleteDatabase($database["name"]);
        }

        $this->object->createDatabase($this->rawOptions["udp"]["database"]);
        $this->object->createDatabase($this->rawOptions["tcp"]["database"]);
    }

    /**
     * @group tcp
     */
    public function testGuzzleHttpApiWorksCorrectly()
    {
        $this->object->mark("tcp.test", ["mark" => "element"]);

        $body = $this->object->query("select * from tcp.test");
        $this->assertCount(1, $body[0]["points"]);
        $this->assertEquals("element", $body[0]["points"][0][2]);
    }

    /**
     * @group tcp
     */
    public function testGuzzleHttpQueryApiWorksCorrectly()
    {
        $this->object->mark("tcp.test", ["mark" => "element"]);

        $body = $this->object->query("select * from tcp.test");

        $this->assertCount(1, $body);
        $this->assertEquals("tcp.test", $body[0]["name"]);
        $this->assertEquals("element", $body[0]["points"][0][2]);
    }

    /**
     * @group tcp
     */
    public function testGuzzleHttpQueryApiWithMultipleData()
    {
        $this->object->mark("tcp.test", ["mark" => "element"]);
        $this->object->mark("tcp.test", ["mark" => "element2"]);
        $this->object->mark("tcp.test", ["mark" => "element3"]);

        $body = $this->object->query("select mark from tcp.test", "s");

        $this->assertCount(3, $body[0]["points"]);
        $this->assertEquals("tcp.test", $body[0]["name"]);
    }

    /**
     * @group tcp
     */
    public function testGuzzleHttpQueryApiWithTimePrecision()
    {
        $this->object->mark("tcp.test", ["mark" => "element"]);

        $body = $this->object->query("select mark from tcp.test", "s");

        $this->assertCount(1, $body[0]["points"]);
        $this->assertEquals("tcp.test", $body[0]["name"]);
    }

    /**
     * @group tcp
     */
    public function testGuzzleHttpWriteApiWithTimePrecision()
    {
        $this->object->mark("tcp.test", ["time" => 1410591552, "mark" => "element"], "s");

        $body = $this->object->query("select mark from tcp.test", "ms");

        $this->assertCount(1, $body[0]["points"]);
        $this->assertEquals("tcp.test", $body[0]["name"]);

        $this->assertEquals("1410591552000", $body[0]["points"][0][0]);
    }

    /**
     * @group udp
     */
    public function testUdpIpWriteData()
    {
        $rawOptions = $this->rawOptions;
        $options = new Options();
        $options->setHost($rawOptions["udp"]["host"]);
        $options->setUsername($rawOptions["udp"]["username"]);
        $options->setPassword($rawOptions["udp"]["password"]);
        $options->setPort($rawOptions["udp"]["port"]);

        $adapter = new UdpAdapter($options);
        $object = new Client();
        $object->setAdapter($adapter);

        $object->mark("udp.test", ["mark" => "element"]);
        $object->mark("udp.test", ["mark" => "element1"]);
        $object->mark("udp.test", ["mark" => "element2"]);
        $object->mark("udp.test", ["mark" => "element3"]);

        // Wait UDP/IP message arrives
        usleep(200e3);

        $this->options->setDatabase("udp.test");
        $body = $this->object->query("select * from udp.test");

        $this->assertCount(4, $body[0]["points"]);
        $this->assertEquals("udp.test", $body[0]["name"]);
    }

    /**
     * @group filter
     */
    public function testColumnsPointsFilterWorksCorrectly()
    {
        $this->object->setFilter(new ColumnsPointsFilter());
        $this->object->mark("tcp.test", ["time" => 1410591552, "mark" => "element"], "s");

        $body = $this->object->query("select mark from tcp.test", "ms");

        $this->assertCount(1, $body);
        $this->assertEquals("element", $body["tcp.test"][0]["mark"]);
        $this->assertSame(1410591552000, $body["tcp.test"][0]["time"]);
    }

    public function testListActiveDatabses()
    {
        $databases = $this->object->getDatabases();

        $this->assertCount(2, $databases);
    }

    public function testCreateANewDatabase()
    {
        $this->object->createDatabase("walter");
        $databases = $this->object->getDatabases();

        $this->assertCount(3, $databases);

        $this->object->deleteDatabase("walter");
    }
}
