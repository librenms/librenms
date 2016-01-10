<?php

namespace InfluxDB\Test;

use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Driver\Guzzle;
use InfluxDB\Point;
use InfluxDB\ResultSet;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class DatabaseTest extends AbstractTest
{

    /**
     * @var string
     */
    protected $dataToInsert;

    /**
     * @var
     */
    protected $mockResultSet;

    public function setUp()
    {
        parent::setUp();

        $this->resultData = file_get_contents(dirname(__FILE__) . '/result.example.json');

        $this->mockClient->expects($this->any())
            ->method('listDatabases')
            ->will($this->returnValue(array('test123', 'test')));

        $this->dataToInsert = file_get_contents(dirname(__FILE__) . '/input.example.json');

    }

    /**
     *
     */
    public function testQuery()
    {
        $testResultSet = new ResultSet($this->resultData);
        $this->assertEquals($this->database->query('SELECT * FROM test_metric'), $testResultSet);
    }

    public function testCreateRetentionPolicy()
    {
        $retentionPolicy = new Database\RetentionPolicy('test', '1d', 1, true);

        $mockClient = $this->getClientMock(true);

        $database = new Database('test', $mockClient);

        $this->assertEquals($database->createRetentionPolicy($retentionPolicy), new ResultSet($this->getEmptyResult()));
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

        $this->assertEquals(true, $this->database->writePoints(array($point1, $point2)));
    }
}