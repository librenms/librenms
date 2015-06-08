<?php
namespace InfluxDB\Adapter;

use InfluxDB\Options;

final class UdpAdapter extends AdapterAbstract
{
    public function send(array $message)
    {
        $message = array_replace_recursive($this->getMessageDefaults(), $message);

        $message = json_encode($message);
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($socket, $message, strlen($message), 0, $this->getOptions()->getHost(), $this->getOptions()->getPort());
        socket_close($socket);
    }
}
