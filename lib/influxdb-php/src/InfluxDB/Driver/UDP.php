<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB\Driver;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class UDP
 *
 * @package InfluxDB\Driver
 */
class UDP implements DriverInterface
{
    /**
     * Parameters
     *
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $config;

    /**
     * @param string $host IP/hostname of the InfluxDB host
     * @param int    $port Port of the InfluxDB process
     */
    public function __construct($host, $port)
    {
        $this->config['host'] = $host;
        $this->config['port'] = $port;


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
     * @return mixed
     */
    public function write($data = null)
    {

        $host = sprintf('udp://%s:%d', $this->config['host'], $this->config['port']);

        // stream the data using UDP and suppress any errors
        $stream = @stream_socket_client($host);
        @stream_socket_sendto($stream, $data);
        @fclose($stream);

        return true;
    }

    /**
     * Should return if sending the data was successful
     *
     * @return bool
     */
    public function isSuccess()
    {
        return true;
    }
}