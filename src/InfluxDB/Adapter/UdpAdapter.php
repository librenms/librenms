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
        $message = json_encode($message);
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($socket, $message, strlen($message), 0, $this->options->getHost(), $this->options->getPort());
        socket_close($socket);
    }
}
