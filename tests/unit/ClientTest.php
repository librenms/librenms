<?php

namespace InfluxDB\Test;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InfluxDB\Client;
use InfluxDB\Driver\Guzzle;

class ClientTest extends \PHPUnit_Framework_TestCase
{

    /** @var Client $client */
    protected $client = null;

    public function testBaseURl()
    {
        $client = new Client('localhost', 8086);

        $this->assertEquals($client->getBaseURI(), 'http://localhost:8086');
    }

    public function testSelectDbShouldReturnDatabaseInstance()
    {
        $client = new Client('localhost', 8086);

        $dbName = 'test-database';
        $database = $client->selectDB($dbName);

        $this->assertInstanceOf('\InfluxDB\Database', $database);

        $this->assertEquals($dbName, $database->getName());
    }


    /**
     */
    public function testGuzzleQuery()
    {
        $client = new Client('localhost', 8086);
        $query = "some-bad-query";

        $bodyResponse = file_get_contents(dirname(__FILE__) . '/result.example.json');
        $httpMockClient = self::buildHttpMockClient($bodyResponse);

        $client->setDriver(new Guzzle($httpMockClient));

        /** @var \InfluxDB\ResultSet $result */
        $result = $client->query(null, $query);

        $this->assertInstanceOf('\InfluxDB\ResultSet', $result);
    }

    /**
     * @return \Guzzle\Http\Client
     */
    public static function buildHttpMockClient($body)
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([new Response(200, array(), $body)]);

        $handler = HandlerStack::create($mock);
        return new GuzzleClient(['handler' => $handler]);
    }
}