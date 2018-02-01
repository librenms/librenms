<?php

namespace InfluxDB\Test;

use InfluxDB\Client;
use InfluxDB\Driver\Guzzle;
use InfluxDB\Point;

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

    public function testGetters()
    {
        $client = $this->getClient();

        $this->assertEquals('http://localhost:8086', $client->getBaseURI());
        $this->assertInstanceOf('InfluxDB\Driver\Guzzle', $client->getDriver());
        $this->assertEquals('localhost', $client->getHost());
        $this->assertEquals('0', $client->getTimeout());
        $this->assertFalse($client->getVerifySSL());
    }

    public function testBaseURl()
    {
        $client = $this->getClient();

        $this->assertEquals($client->getBaseURI(), 'http://localhost:8086');
    }

    public function testSelectDbShouldReturnDatabaseInstance()
    {
        $client = $this->getClient();

        $dbName = 'test-database';
        $database = $client->selectDB($dbName);

        $this->assertInstanceOf('\InfluxDB\Database', $database);

        $this->assertEquals($dbName, $database->getName());
    }

    public function testSecureInstance()
    {
        $client = $this->getClient('test', 'test', true);
        $urlParts = parse_url($client->getBaseURI());

        $this->assertEquals('https', $urlParts['scheme']);
    }

    /**
     */
    public function testGuzzleQuery()
    {
        $client = $this->getClient('test', 'test');
        $query = "some-bad-query";

        $bodyResponse = file_get_contents(dirname(__FILE__) . '/json/result.example.json');
        $httpMockClient = $this->buildHttpMockClient($bodyResponse);

        $guzzle = new Guzzle($httpMockClient);
        $client->setDriver($guzzle);

        /** @var \InfluxDB\ResultSet $result */
        $result = $client->query('somedb', $query);

        $parameters = $client->getDriver()->getParameters();

        $this->assertEquals(['test', 'test'], $parameters['auth']);
        $this->assertEquals('somedb', $parameters['database']);
        $this->assertInstanceOf('\InfluxDB\ResultSet', $result);

        $point = new Point('test', 1.0);

        $this->assertEquals(
            true,
            $client->write(
                [
                    'url' => 'http://localhost',
                    'database' => 'influx_test_db',
                    'method' => 'post'
                ],
                (string) $point
            )
        );

        $this->assertEquals(
            true,
            $client->write(
                [
                    'url' => 'http://localhost',
                    'database' => 'influx_test_db',
                    'method' => 'post'
                ],
                [(string) $point]
            )
        );

        $this->expectException('\InvalidArgumentException');
        $client->query('test', 'bad-query');

        $this->expectException('\InfluxDB\Driver\Exception');
        $client->query('test', 'bad-query');
    }

    public function testGetLastQuery()
    {
        $this->mockClient->query('test', 'SELECT * from test_metric');
        $this->assertEquals($this->getClient()->getLastQuery(), 'SELECT * from test_metric');
    }

    public function testListDatabases()
    {
        $this->doTestResponse('databases.example.json', ['test', 'test1', 'test2'], 'listDatabases');
    }
    public function testListUsers()
    {
        $this->doTestResponse('users.example.json', ['user', 'admin'], 'listUsers');
    }

    public function testFactoryMethod()
    {
        $client = $this->getClient('test', 'test', true);

        $staticClient = \InfluxDB\Client::fromDSN('https+influxdb://test:test@localhost:8086/');

        $this->assertEquals($client, $staticClient);

        $db = $client->selectDB('testdb');
        $staticDB = \InfluxDB\Client::fromDSN('https+influxdb://test:test@localhost:8086/testdb');

        $this->assertEquals($db, $staticDB);

    }

    public function testTimeoutIsFloat()
    {
        $client =  $this->getClient('test', 'test', false, false, 0.5);

        $this->assertEquals(0.5, $client->getTimeout());
    }

    public function testVerifySSLIsBoolean()
    {
        $client =  $this->getClient('test', 'test', true, true);

        $this->assertTrue($client->getVerifySSL());
    }


    /**
     * @param string $responseFile
     * @param array  $result
     * @param string $method
     */
    protected function doTestResponse($responseFile, array $result, $method)
    {
        $client = $this->getClient();
        $bodyResponse = file_get_contents(dirname(__FILE__) . '/json/'. $responseFile);
        $httpMockClient = $this->buildHttpMockClient($bodyResponse);

        $client->setDriver(new Guzzle($httpMockClient));

        $this->assertEquals($result, $client->$method());
    }

    /**
     * @param string     $username
     * @param string     $password
     * @param bool|false $ssl
     * @param int $timeout
     *
     * @return Client
     */
    protected function getClient($username = '', $password = '',  $ssl = false, $verifySSL = false, $timeout = 0)
    {
        return new Client('localhost', 8086, $username, $password, $ssl, $verifySSL, $timeout);
    }

}
