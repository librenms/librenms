<?php

namespace InfluxDB\Test;

use InfluxDB\Client;
use InfluxDB\Driver\Guzzle;

class ClientTest extends AbstractTest
{

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
    }

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
        $httpMockClient = $this->buildHttpMockClient($bodyResponse);

        $client->setDriver(new Guzzle($httpMockClient));

        /** @var \InfluxDB\ResultSet $result */
        $result = $client->query(null, $query);

        $this->assertInstanceOf('\InfluxDB\ResultSet', $result);
    }

}