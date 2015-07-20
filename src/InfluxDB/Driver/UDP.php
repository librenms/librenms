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

        // create a socket connection to check if InfluxDB is alive
        $socket = fsockopen('udp://' .$host, $port);
        if (!$socket) {
            throw new Exception('There is no InfluxDB UDP service running on port '. $port);
        }

        fclose($socket);
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
    public function send($data = null)
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($socket, $data, strlen($data), 0, $this->config['host'], $this->config['port']);
        socket_close($socket);

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