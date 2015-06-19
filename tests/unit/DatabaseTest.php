<?php

namespace Leaseweb\InfluxDB\Test;


use Leaseweb\InfluxDB\Client;
use Leaseweb\InfluxDB\Database;
use Leaseweb\InfluxDB\Point;

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

    /**
     * todo:
     */
    public function testWrite()
    {
        $point1 = new Point(
            'cpu_load_short',
            array('host'  =>'server01', 'region'=>'us-west'),
            array('value' => 0.64),
            'myTime'
        );

        $point2 = new Point(
            'cpu_load_short',
            array('host'  =>'server01', 'region'=>'us-west'),
            array('value' => 0.84),
            'myTime'
        );

        $this->db->writePoints(array($point1, $point2));

        $this->assertTrue(
            true
        );
    }
}