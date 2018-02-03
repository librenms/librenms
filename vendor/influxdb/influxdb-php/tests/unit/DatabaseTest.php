<?php

namespace InfluxDB\Test;

use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Point;
use InfluxDB\ResultSet;

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

        $this->resultData = file_get_contents(dirname(__FILE__) . '/json/result.example.json');

        $this->mockClient->expects($this->any())
            ->method('listDatabases')
            ->will($this->returnValue(array('test123', 'test')));

        $this->dataToInsert = file_get_contents(dirname(__FILE__) . '/json/input.example.json');

    }

    public function testGetters()
    {
        $this->assertInstanceOf('InfluxDB\Client', $this->database->getClient());
        $this->assertInstanceOf('InfluxDB\Query\Builder', $this->database->getQueryBuilder());
    }


    /**
     *
     */
    public function testQueries()
    {
        $testResultSet = new ResultSet($this->resultData);
        $this->assertEquals($this->database->query('SELECT * FROM test_metric'), $testResultSet);

        $this->database->drop();
        $this->assertEquals('DROP DATABASE "influx_test_db"', Client::$lastQuery);

    }


    public function testRetentionPolicyQueries()
    {
        $retentionPolicy = $this->getTestRetentionPolicy();

        $this->assertEquals(
            $this->getTestDatabase()->createRetentionPolicy($retentionPolicy),
            new ResultSet($this->getEmptyResult())
        );

        $this->database->listRetentionPolicies();
        $this->assertEquals('SHOW RETENTION POLICIES ON "influx_test_db"', Client::$lastQuery);

        $this->database->alterRetentionPolicy($this->getTestRetentionPolicy());
        $this->assertEquals(
            'ALTER RETENTION POLICY "test" ON "influx_test_db" DURATION 1d REPLICATION 1 DEFAULT',
            Client::$lastQuery
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyDatabaseName()
    {
        new Database(null, $this->mockClient);
    }

    /**
     * @group legacy
     * @expectedDeprecation Unsilenced deprecation: The $createIfNotExists parameter to Database::create is deprecated
     */
    public function testIfNotExistsDeprecation()
    {
        $this->database->create(null, true);
    }

    public function testCreate()
    {
        // test create with retention policy
        $this->database->create($this->getTestRetentionPolicy('influx_test_db'));
        $this->assertEquals(
            'CREATE RETENTION POLICY "influx_test_db" ON "influx_test_db" DURATION 1d REPLICATION 1 DEFAULT',
            Client::$lastQuery
        );

        // test creating a database without parameters
        $this->database->create(null);
        $this->assertEquals('CREATE DATABASE "influx_test_db"', Client::$lastQuery);


        $this->mockClient->expects($this->any())
            ->method('query')
            ->will($this->returnCallback(function () {
                throw new \Exception('test exception');
            }));


        // test an exception being handled correctly
        $this->expectException('\InfluxDB\Database\Exception');
        $this->database->create($this->getTestRetentionPolicy('influx_test_db'), false);

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

        $payload = [
            (string) $point1,
            (string) $point2
        ];

        $this->assertEquals(true, $this->database->writePayload($payload));


        $this->mockClient->expects($this->once())
            ->method('write')
            ->will($this->throwException(new \Exception('Test exception')));

        $this->expectException('InfluxDB\Exception');

        $this->database->writePoints(array($point1, $point2));

    }

    public function testQueryBuilderOrderBy()
    {
        $this->assertEquals(
            $this->database->getQueryBuilder()
                ->from('test_metric')
                ->orderBy('time', 'DESC')->getQuery(),
            'SELECT * FROM "test_metric" ORDER BY time DESC');

        $this->assertEquals(
            $this->database->getQueryBuilder()
                ->from('test_metric')
                ->orderBy('time', 'DESC')
                ->orderBy('some_field', 'ASC')
                ->getQuery(),
            'SELECT * FROM "test_metric" ORDER BY time DESC,some_field ASC');
    }

    /**
     * @see https://github.com/influxdata/influxdb-php/pull/36
     */
    public function testReservedNames()
    {
        $database = new Database('stats', $this->mockClient);

        // test handling of reserved keywords in database name
        $database->create();
        $this->assertEquals('CREATE DATABASE "stats"', Client::$lastQuery);

        $database->listRetentionPolicies();
        $this->assertEquals('SHOW RETENTION POLICIES ON "stats"', Client::$lastQuery);

        // test handling of reserved keywords in retention policy names
        $database->create($this->getTestRetentionPolicy('default'));
        $this->assertEquals(
            'CREATE RETENTION POLICY "default" ON "stats" DURATION 1d REPLICATION 1 DEFAULT',
            Client::$lastQuery
        );

        // test handling of reserved keywords in measurement names
        $this->assertEquals($database->getQueryBuilder()->from('server')->getQuery(), 'SELECT * FROM "server"');
    }

    /**
     * @param string $name
     *
     * @return Database
     */
    protected function getTestDatabase($name = 'test')
    {
        return new Database($name, $this->getClientMock(true));
    }

    /**
     * @param string $name
     *
     * @return Database\RetentionPolicy
     */
    protected function getTestRetentionPolicy($name = 'test')
    {
        return new Database\RetentionPolicy($name, '1d', 1, true);
    }
}
