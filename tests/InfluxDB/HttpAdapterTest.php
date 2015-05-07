<?php
namespace InfluxDB;

use InfluxDB\Adapter\HttpAdapter;
use InfluxDB\Adapter\UdpAdapter;
use InfluxDB\Filter\ColumnsPointsFilter;

class HttpAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $rawOptions;
    private $object;
    private $options;

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

        $adapter = new HttpAdapter($options);

        $influx = new Client();
        $influx->setAdapter($adapter);
        $this->object = $influx;

        $databases = $this->object->getDatabases();
        foreach ($databases as $database) {
            $this->object->deleteDatabase($database["name"]);
        }

        $this->object->createDatabase($this->rawOptions["tcp"]["database"]);
    }

    /**
     * @group tcp
     */
    public function testApiWorksCorrectly()
    {
        $this->object->mark("tcp.test", ["mark" => "element"]);

        $body = $this->object->query("select * from tcp.test");
        $this->assertCount(1, $body[0]["points"]);
        $this->assertEquals("element", $body[0]["points"][0][2]);
    }

    /**
     * @group tcp
     */
    public function testQueryApiWorksCorrectly()
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
    public function testQueryApiWithMultipleData()
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
    public function testQueryApiWithTimePrecision()
    {
        $this->object->mark("tcp.test", ["mark" => "element"]);

        $body = $this->object->query("select mark from tcp.test", "s");

        $this->assertCount(1, $body[0]["points"]);
        $this->assertEquals("tcp.test", $body[0]["name"]);
    }

    /**
     * @group tcp
     */
    public function testWriteApiWithTimePrecision()
    {
        $this->object->mark("tcp.test", ["time" => 1410591552, "mark" => "element"], "s");

        $body = $this->object->query("select mark from tcp.test", "ms");

        $this->assertCount(1, $body[0]["points"]);
        $this->assertEquals("tcp.test", $body[0]["name"]);

        $this->assertEquals("1410591552000", $body[0]["points"][0][0]);
    }

    public function testListActiveDatabses()
    {
        $databases = $this->object->getDatabases();

        $this->assertCount(1, $databases);
    }

    public function testCreateANewDatabase()
    {
        $this->object->createDatabase("walter");
        $databases = $this->object->getDatabases();

        $this->assertCount(2, $databases);

        $this->object->deleteDatabase("walter");
    }
}
