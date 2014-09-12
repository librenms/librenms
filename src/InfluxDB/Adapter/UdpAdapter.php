<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;

class UdpAdapter implements AdapterInterface
{
    private $options;

    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    public function send($message)
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $message = json_encode($message);
        socket_sendto($socket, $message, strlen($message), 0, $this->options->getHost(), $this->options->getPort());
        socket_close($socket);
    }
}
