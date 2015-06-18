<?php

namespace Leaseweb\InfluxDB\Test;


use Leaseweb\InfluxDB\Client;
use Leaseweb\InfluxDB\Database;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{

    /** @var Database $db */
    protected $db = null;

    /** @var  Client $client */
    protected $mockClient;

    protected $dataToInsert;

    public function setUp()
    {
        $this->mockClient = $this->getMockBuilder('\Leaseweb\InfluxDB\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockClient->expects($this->any())
            ->method('getBaseURI')
            ->will($this->returnValue($this->equalTo('http://localhost:8086')));

        $this->db = new Database('influx_test_db', $this->mockClient);

        $this->dataToInsert = file_get_contents(dirname(__FILE__) . '/input.example.json');

    }

    public function testWrite()
    {
        $this->assertTrue(
            'mockClient'
        );
    }
}