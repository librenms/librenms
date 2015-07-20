<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB\Driver;

use GuzzleHttp\Client;
use Guzzle\Http\Message\Response;
use GuzzleHttp\Psr7\Request;
use InfluxDB\ResultSet;

class Guzzle implements DriverInterface, QueryDriverInterface
{

    /**
     * Array of options
     *
     * @var array
     */
    private $parameters;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var Response
     */
    private $response;

    /**
     * Set the config for this driver
     *
     * @param Client $client
     *
     * @return mixed
     */
    public function __construct(Client $client)
    {
        $this->httpClient = $client;
    }

    /**
     * Called by the client write() method, will pass an array of required parameters such as db name
     *
     * will contain the following parameters:
     *
     * [
     *  'database' => 'name of the database',
     *  'url' => 'URL to the resource',
     *  'method' => 'HTTP method used'
     * ]
     *
     * @param array $parameters
     *
     * @return mixed
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Send the data
     *
     * @param $data
     *
     * @throws Exception
     * @return mixed
     */
    public function send($data = null)
    {
        $requestObject = new Request($this->parameters['method'], $this->parameters['url'], [], $data);

        $this->response = $this->httpClient->send($requestObject);
    }

    /**
     * Should return if sending the data was successful
     *
     * @return bool
     */
    public function isSuccess()
    {
        return in_array($this->response->getStatusCode(), ['200', '204']);
    }

    /**
     * @return ResultSet
     */
    public function getResultSet()
    {
        $raw = (string) $this->response->getBody(true);

        return new ResultSet($raw);
    }
}
