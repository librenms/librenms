<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;

class UdpAdapter implements AdapterInterface, ConnectableInterface
{
    private $options;
    private $socket;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function connect()
    {
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        return $this;
    }

    public function disconnect()
    {
        socket_close($this->getSocket());
    }

    public function send($message)
    {
        $message = json_encode($message);
        socket_sendto($this->getSocket(), $message, strlen($message), 0, $this->host, $this->port);
    }
}
