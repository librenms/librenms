<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB\Test;


use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Driver\Guzzle as GuzzleDriver;
use InfluxDB\ResultSet;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;

/**
 * @property mixed resultData
 */
abstract class AbstractTest extends TestCase
{

    /** @var  Client|PHPUnit_Framework_MockObject_MockObject $client */
    protected $mockClient;

    /**
     * @var string
     */
    protected $emptyResult = '{"results":[{}]}';

    /**
     * @var ResultSet
     */
    protected $mockResultSet;

    /** @var Database $database */
    protected $database = null;

    public function setUp()
    {
        $this->mockClient = $this->getMockBuilder('\InfluxDB\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultData = file_get_contents(dirname(__FILE__) . '/json/result.example.json');

        $this->mockClient->expects($this->any())
            ->method('getBaseURI')
            ->will($this->returnValue($this->equalTo('http://localhost:8086')));

        $this->mockClient->expects($this->any())
            ->method('query')
            ->will($this->returnCallback(function ($db, $query) {
                Client::$lastQuery = $query;

                return new ResultSet($this->resultData);
            }));

        $this->mockClient->expects($this->any())
            ->method('write')
            ->will($this->returnValue(true));

        $httpMockClient = new GuzzleDriver($this->buildHttpMockClient(''));

        // make sure the client has a valid driver
        $this->mockClient->expects($this->any())
            ->method('getDriver')
            ->will($this->returnValue($httpMockClient));

        $this->database = new Database('influx_test_db', $this->mockClient);

    }

    /**
     * @return mixed
     */
    public function getMockResultSet()
    {
        return $this->mockResultSet;
    }

    /**
     * @param mixed $mockResultSet
     */
    public function setMockResultSet($mockResultSet)
    {
        $this->mockResultSet = $mockResultSet;
    }


    /**
     * @return GuzzleClient
     */
    public function buildHttpMockClient($body)
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, array(), $body),
            new Response(200, array(), $body),
            new Response(200, array(), $body),
            new Response(400, array(), 'fault{'),
            new Response(400, array(), $body),
            new Response(400, array(), $body),
        ]);

        $handler = HandlerStack::create($mock);
        return new GuzzleClient(['handler' => $handler]);
    }

    /**
     * @return string
     */
    public function getEmptyResult()
    {
        return $this->emptyResult;
    }

    /**
     * @param bool $emptyResult
     *
     * @return PHPUnit_Framework_MockObject_MockObject|Client
     */
    public function getClientMock($emptyResult = false)
    {
        $mockClient = $this->getMockBuilder('\InfluxDB\Client')
            ->disableOriginalConstructor()
            ->getMock();

        if ($emptyResult) {
            $mockClient->expects($this->any())
                ->method('query')
                ->will($this->returnValue(new ResultSet($this->getEmptyResult())));
        }

        return $mockClient;
    }

    /**
     * We need this to be compatible with PHPUnit 4.8, 5.x and 6x.
     * To be removed when we drop support for PHP 5.5.
     *
     * @param string $class
     */
    public function expectException($class)
    {
        if (is_callable('parent::expectException')) {
            parent::expectException($class);
        } else {
            $this->setExpectedException($class);
        }
    }
}