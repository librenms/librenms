<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB\Test;


use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Driver\Guzzle;
use InfluxDB\ResultSet;
use PHPUnit_Framework_MockObject_MockObject;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;

/**
 * @property mixed resultData
 */
abstract class AbstractTest extends \PHPUnit_Framework_TestCase
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

        $this->resultData = file_get_contents(dirname(__FILE__) . '/result.example.json');

        $this->mockClient->expects($this->any())
            ->method('getBaseURI')
            ->will($this->returnValue($this->equalTo('http://localhost:8086')));

        $this->mockClient->expects($this->any())
            ->method('query')
            ->will($this->returnValue(new ResultSet($this->resultData)));

        $httpMockClient = new Guzzle($this->buildHttpMockClient(''));

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
        $mock = new MockHandler([new Response(200, array(), $body)]);

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
            $mockClient->expects($this->once())
                ->method('query')
                ->will($this->returnValue(new ResultSet($this->getEmptyResult())));
        }

        return $mockClient;
    }
}