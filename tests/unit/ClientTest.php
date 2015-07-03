<?php

namespace InfluxDB\Test;

use InfluxDB\Client;

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
}