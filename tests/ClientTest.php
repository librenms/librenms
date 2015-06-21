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
    private $rawOptions;
    private $object;
    private $options;

    private $anotherClient;

    public function setUp()
    {
        $options = include __DIR__ . '/bootstrap.php';
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

        $influx = new Client($adapter);
        $this->object = $influx;

        $databases = $this->object->getDatabases();
        if (array_key_exists("values", $databases["results"][0]["series"][0])) {
            foreach ($databases["results"][0]["series"][0]["values"] as $database) {
                $this->object->deleteDatabase($database[0]);
            }
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

        sleep(1);

        $body = $this->object->query("select * from \"tcp.test\"");
        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("mark", $body["results"][0]["series"][0]["columns"][1]);
        $this->assertEquals("element", $body["results"][0]["series"][0]["values"][0][1]);
    }

    /**
     * @group tcp
     * @group proxy
     */
    public function testGuzzleHttpApiWorksCorrectlyWithProxies()
    {
        $this->options->setHost("localhost");
        $this->options->setPort(9000);
        $this->options->setPrefix("/influxdb");
        $this->object->mark("tcp.test", ["mark" => "element"]);

        sleep(2);

        $body = $this->object->query("select * from \"tcp.test\"");
        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("mark", $body["results"][0]["series"][0]["columns"][1]);
        $this->assertEquals("element", $body["results"][0]["series"][0]["values"][0][1]);
    }

    /**
     * @group tcp
     */
    public function testGuzzleHttpQueryApiWorksCorrectly()
    {
        $this->object->mark("tcp.test", ["mark" => "element"]);

        sleep(1);

        $body = $this->object->query("select * from \"tcp.test\"");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("mark", $body["results"][0]["series"][0]["columns"][1]);
        $this->assertEquals("element", $body["results"][0]["series"][0]["values"][0][1]);
    }

    /**
     * @group tcp
     */
    public function testGuzzleHttpQueryApiWithMultipleData()
    {
        $this->object->mark("tcp.test", ["mark" => "element"]);
        $this->object->mark("tcp.test", ["mark" => "element2"]);
        $this->object->mark("tcp.test", ["mark" => "element3"]);

        sleep(1);

        $body = $this->object->query("select mark from \"tcp.test\"", "s");

        $this->assertCount(3, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("mark", $body["results"][0]["series"][0]["columns"][1]);
        $this->assertEquals("element", $body["results"][0]["series"][0]["values"][0][1]);
    }

    /**
     * @group tcp
     */
    public function testWriteDirectMessages()
    {
        $this->object->mark([
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

        sleep(1);

        $body = $this->object->query("select * from \"vm-serie\"");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("cpu", $body["results"][0]["series"][0]["columns"][1]);
        $this->assertEquals(18.12, $body["results"][0]["series"][0]["values"][0][1]);
    }

    /**
     * @group tcp
     */
    public function testOverrideDatabaseNameViaMessage()
    {
        $this->options->setDatabase("a-wrong-database");

        $this->object->mark([
            "database" => "tcp.test",
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

        sleep(1);

        $this->options->setDatabase("tcp.test");
        $body = $this->object->query("select * from \"vm-serie\"");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("cpu", $body["results"][0]["series"][0]["columns"][1]);
        $this->assertEquals(18.12, $body["results"][0]["series"][0]["values"][0][1]);
        $this->assertEquals(712423, $body["results"][0]["series"][0]["values"][0][2]);
    }

    /**
     * @group udp
     */
    public function testUdpIpWriteData()
    {
        $object = $this->createClientWithUdpAdapter();

        $object->mark("udp.test", ["mark" => "element"]);
        $object->mark("udp.test", ["mark" => "element1"]);
        $object->mark("udp.test", ["mark" => "element2"]);
        $object->mark("udp.test", ["mark" => "element3"]);

        // Wait UDP/IP message arrives
        sleep(2);

        $this->options->setDatabase("udp.test");
        $body = $this->object->query("select * from \"udp.test\"");

        $this->assertCount(4, $body["results"][0]["series"][0]["values"]);
    }

    /**
     * @group udp
     */
    public function testSendMultipleMeasurementWithUdpIp()
    {
        $object = $this->createClientWithUdpAdapter();

        $object->mark([
            "points" => [
                [
                    "measurement" => "mem",
                    "fields" => [
                        "free" => 712423,
                    ],
                ],
                [
                    "measurement" => "cpu",
                    "fields" => [
                        "cpu" => 18.12,
                    ],
                ],
            ]
        ]);

        sleep(2);

        $this->options->setDatabase("udp.test");
        $body = $this->object->query("select * from \"cpu\"");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("cpu", $body["results"][0]["series"][0]["columns"][1]);
        $this->assertEquals(18.12, $body["results"][0]["series"][0]["values"][0][1]);

        $body = $this->object->query("select * from \"mem\"");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("free", $body["results"][0]["series"][0]["columns"][1]);
        $this->assertEquals(712423, $body["results"][0]["series"][0]["values"][0][1]);
    }

    /**
     * @group udp
     */
    public function testWriteDirectMessageWithUdpIp()
    {
        $object = $this->createClientWithUdpAdapter();

        $object->mark([
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

        sleep(2);

        $this->options->setDatabase("udp.test");
        $body = $this->object->query("select * from \"vm-serie\"");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("cpu", $body["results"][0]["series"][0]["columns"][1]);
        $this->assertEquals(18.12, $body["results"][0]["series"][0]["values"][0][1]);
        $this->assertEquals(712423, $body["results"][0]["series"][0]["values"][0][2]);
    }

    /**
     * @group udp
     * @group date
     */
    public function testWriteDirectMessageWillPreserveActualTime()
    {
        $object = $this->createClientWithUdpAdapter();

        $object->mark([
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

        sleep(2);

        $this->options->setDatabase("udp.test");
        $body = $this->object->query("select * from \"vm-serie\"");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("time", $body["results"][0]["series"][0]["columns"][0]);
        $saved = $body["results"][0]["series"][0]["values"][0][0];
        $this->assertRegExp("/".date("Y-m-d")."/i", $saved);
    }

    /**
     * @group udp
     * @group date
     */
    public function testWriteDirectMessageWillPreserveDatetime()
    {
        $object = $this->createClientWithUdpAdapter();

        $object->mark([
            "time" => "2009-11-10T23:00:00Z",
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

        sleep(2);

        $this->options->setDatabase("udp.test");
        $body = $this->object->query("select * from \"vm-serie\"");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("time", $body["results"][0]["series"][0]["columns"][0]);
        $this->assertEquals("2009-11-10T23:00:00Z", $body["results"][0]["series"][0]["values"][0][0]);
    }

    /**
     * @group udp
     * @group tags
     */
    public function testTagsAreWrittenCorrectly()
    {
        $object = $this->createClientWithUdpAdapter();

        $object->mark([
            "tags" => [
                "region"  => "eu",
            ],
            "points" => [
                [
                    "measurement" => "vm-serie",
                    "tags" => [
                        "dc"  => "eu-west-1",
                        "one"  => "two",
                    ],
                    "fields" => [
                        "cpu" => 18.12,
                        "free" => 712423,
                    ],
                ],
                [
                    "measurement" => "vm-serie",
                    "tags" => [
                        "dc"  => "us-east-1",
                    ],
                    "fields" => [
                        "cpu" => 28.12,
                        "free" => 412923,
                    ],
                ],
            ]
        ]);

        sleep(2);

        $this->options->setDatabase("udp.test");
        $body = $this->object->query("select * from \"vm-serie\" where dc='eu-west-1'");

        $this->assertCount(1, $body["results"][0]["series"][0]["values"]);
        $this->assertEquals("cpu", $body["results"][0]["series"][0]["columns"][1]);
        $this->assertEquals(18.12, $body["results"][0]["series"][0]["values"][0][1]);
        $this->assertEquals(712423, $body["results"][0]["series"][0]["values"][0][2]);
    }

    public function testListActiveDatabses()
    {
        $databases = $this->object->getDatabases();

        $this->assertCount(2, $databases["results"][0]["series"][0]["values"]);
    }

    public function testCreateANewDatabase()
    {
        $this->object->createDatabase("walter");

        sleep(1);

        $databases = $this->object->getDatabases();

        $this->assertCount(3, $databases["results"][0]["series"][0]["values"]);

        $this->object->deleteDatabase("walter");
    }

    private function createClientWithUdpAdapter()
    {
        $rawOptions = $this->rawOptions;
        $options = new Options();
        $options->setHost($rawOptions["udp"]["host"]);
        $options->setUsername($rawOptions["udp"]["username"]);
        $options->setPassword($rawOptions["udp"]["password"]);
        $options->setPort($rawOptions["udp"]["port"]);
        $options->setDatabase($rawOptions["udp"]["database"]);

        $adapter = new UdpAdapter($options);
        $object = new Client($adapter);

        return $object;
    }
}
