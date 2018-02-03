<?php
/**
 * @author Stephen "TheCodeAssassin" Hoogendijk
 */

namespace InfluxDB\Driver;

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
     *
     * @var resource
     */
    private $stream;

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
     * Close the stream (if created)
     */
    public function __destruct()
    {
        if (isset($this->stream) && is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function write($data = null)
    {
        if (isset($this->stream) === false) {
            $this->createStream();
        }

        @stream_socket_sendto($this->stream, $data);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccess()
    {
        return true;
    }

    /**
     * Create the resource stream
     */
    protected function createStream()
    {
        $host = sprintf('udp://%s:%d', $this->config['host'], $this->config['port']);

        // stream the data using UDP and suppress any errors
        $this->stream = @stream_socket_client($host);
    }

}
