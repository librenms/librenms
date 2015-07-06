<?php

namespace InfluxDB\Test;

use InfluxDB\Client;
use InfluxDB\ResultSet;

class ClientTest extends \PHPUnit_Framework_TestCase
{

    /** @var Client $client */
    protected $client = null;
    
    public function testBaseURl()
    {
        $client = new Client('localhost', 8086);

        $this->assertEquals(
            $client->getBaseURI(), 'http://localhost:8086'
        );
    }

    public function testSelectDbShouldReturnDatabaseInstance()
    {
        $client = new Client('localhost', 8086);

        $dbName = 'test-database';
        $db = $client->selectDB($dbName);

        $this->assertInstanceOf('\InfluxDB\Database', $db);

        $this->assertEquals($dbName, $db->getName());
    }


    /**
     */
    public function testQuery()
    {
        $client = new Client('localhost', 8086);
        $query = "some-bad-query";

        $bodyResponse = file_get_contents(dirname(__FILE__) . '/result.example.json');
        $httpMockClient = $this->buildHttpMockClient($bodyResponse);

        $client->setHttpClient($httpMockClient);

        /** @var \InfluxDB\ResultSet $result */
        $result = $client->query(null, $query);

        $this->assertInstanceOf('\InfluxDB\ResultSet', $result);
    }

    /**
     * @return \Guzzle\Http\Client
     */
    protected function buildHttpMockClient($body)
    {
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $response= new \Guzzle\Http\Message\Response(200);
        $response->setBody($body);
        $plugin->addResponse($response);
        $mockedClient = new \Guzzle\Http\Client();
        $mockedClient->addSubscriber($plugin);

        return $mockedClient;
    }
}