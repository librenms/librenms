<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB\Driver;

use GuzzleHttp\Client;
use Guzzle\Http\Message\Response;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use InfluxDB\ResultSet;

/**
 * Class Guzzle
 *
 * @package InfluxDB\Driver
 */
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
    public function write($data = null)
    {
        $this->response = $this->httpClient->post($this->parameters['url'], $this->getRequestParameters($data));
    }

    /**
     * @throws Exception
     * @return ResultSet
     */
    public function query()
    {

        $response = $this->httpClient->get($this->parameters['url'], $this->getRequestParameters());

        $raw = (string) $response->getBody();

        $responseJson = json_encode($raw);

        if (isset($responseJson->error)) {
            throw new Exception($responseJson->error);
        }

        return new ResultSet($raw);

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
     * @param null $data
     *
     * @return array
     */
    protected function getRequestParameters($data = null)
    {
        $requestParameters = ['http_errors' => false];

        if ($data) {
            $requestParameters += ['body' => $data];
        }

        if (isset($this->parameters['auth'])) {
            $requestParameters += ['auth' => $this->parameters['auth']];
        }

        return $requestParameters;
    }
}
