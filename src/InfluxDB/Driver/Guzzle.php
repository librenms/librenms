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
     *
     * Configuration for this driver
     *
     * @var array
     */
    private $config;

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
     * @param array $config
     *
     * @return mixed
     */
    public function __construct(array $config)
    {
        $this->httpClient = new Client([
           'timeout' => $config['timeout'],
           'verify' => $config['verify'],
           'base_uri' => $config['base_uri']
        ]);

        $this->config = $config;
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
