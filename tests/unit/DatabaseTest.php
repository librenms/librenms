<?php

namespace InfluxDB\Test;


use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Point;
use InfluxDB\ResultSet;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class DatabaseTest extends PHPUnit_Framework_TestCase
{

    /** @var Database $db */
    protected $db = null;

    /** @var  Client|PHPUnit_Framework_MockObject_MockObject $client */
    protected $mockClient;

    /**
     * @var string
     */
    protected $dataToInsert;

    /**
     * @var string
     */
    protected $resultData;

    /**
     * @var string
     */
    static $emptyResult = '{"results":[{}]}';

    /**
     * @var
     */
    protected $mockResultSet;

    public function setUp()
    {
        $this->mockClient = $this->getMockBuilder('\InfluxDB\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultData = file_get_contents(dirname(__FILE__) . '/result.example.json');

        $this->mockClient->expects($this->any())
            ->method('getBaseURI')
            ->will($this->returnValue($this->equalTo('http://localhost:8086')));

        $this->mockClient->expects($this->any())
            ->method('query')
            ->will($this->returnValue(new ResultSet($this->resultData)));


        $this->mockClient->expects($this->any())
            ->method('listDatabases')
            ->will($this->returnValue(array('test123', 'test')));


        $this->db = new Database('influx_test_db', $this->mockClient);

        $this->dataToInsert = file_get_contents(dirname(__FILE__) . '/input.example.json');

    }

    /**
     *
     */
    public function testQuery()
    {
        $testResultSet = new ResultSet($this->resultData);
        $this->assertEquals($this->db->query('SELECT * FROM test_metric'), $testResultSet);
    }

    public function testCreateRetentionPolicy()
    {
        $retentionPolicy = new Database\RetentionPolicy('test', '1d', 1, true);

        $mockClient = $this->getMockBuilder('\InfluxDB\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $mockClient->expects($this->once())
            ->method('query')
            ->will($this->returnValue(new ResultSet(self::$emptyResult)));



        $database = new Database('test', $mockClient);

        $this->assertEquals($database->createRetentionPolicy($retentionPolicy), new ResultSet(self::$emptyResult));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyDatabaseName()
    {
        new Database(null, $this->mockClient);
    }

    public function testExists()
    {
        $database = new Database('test', $this->mockClient);

        $this->assertEquals($database->exists(), true);
    }


    public function testNotExists()
    {
        $database = new Database('test_not_exists', $this->mockClient);

        $this->assertEquals($database->exists(), false);
    }

    public function testWritePointsInASingleCall()
    {
        $point1 = new Point(
            'cpu_load_short',
            0.64,
            array('host' => 'server01', 'region' => 'us-west'),
            array('cpucount' => 10),
            1435222310
        );

        $point2 = new Point(
            'cpu_load_short',
            0.84
        );

        $payloadExpected ="$point1\n$point2";

        $this->mockClient->expects($this->once())
            ->method('write')
            ->with($this->equalTo($this->db->getName()), $this->equalTo($payloadExpected))
            ->will($this->returnValue(true));

        $this->db->writePoints(array($point1, $point2));
    }
}