<?php
namespace InfluxDB;

use InfluxDB\Adapter\GuzzleAdapter as InfluxHttpAdapter;
use InfluxDB\Options;
use GuzzleHttp\Client as GuzzleHttpClient;
use crodas\InfluxPHP\Client as Crodas;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $object;
    private $options;

    private $anotherClient;

    public function setUp()
    {
        $options = include __DIR__ . '/../bootstrap.php';
        $this->options = $options;

        $this->object = new Client();

        $client = new Crodas(
            $options["tcp"]["host"],
            $options["tcp"]["port"],
            $options["tcp"]["username"],
            $options["tcp"]["password"]
        );
        try {
            $client->deleteDatabase($options["tcp"]["database"]);
        } catch (\Exception $e) {
            // nothing...
        }
        $client->createDatabase($options["tcp"]["database"]);

        $this->anotherClient = $client;
    }

    public function testGuzzleHttpApiWorksCorrectly()
    {
        $tcpOptions = $this->options["tcp"];

        $options = new Options();
        $options->setHost($tcpOptions["host"]);
        $options->setPort($tcpOptions["port"]);
        $options->setUsername($tcpOptions["username"]);
        $options->setPassword($tcpOptions["password"]);

        $guzzleHttp = new GuzzleHttpClient();
        $adapter = new InfluxHttpAdapter($guzzleHttp, $options);
        $adapter->setDatabase($tcpOptions["database"]);

        $influx = new Client();
        $influx->setAdapter($adapter);

        $influx->mark("tcp.test", ["mark" => "element"]);

        $cursor = $this->anotherClient->getDatabase($tcpOptions["database"])
            ->query("select * from tcp.test");
        $this->assertCount(1, $cursor);
        $this->assertEquals("element", $cursor[0]->mark);
    }
}
