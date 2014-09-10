<?php

namespace InfluxDB\Adapter;

class Udp implements AdapterInterface
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $name = __NAMESPACE__;
    private $socket;

    public function __construct(
        $host = "127.0.0.1",
        $port = 5551, 
        $user = "root", 
        $password = "root"
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $user;
        $this->password = $password;
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function getName()
    {
        return $this->name; 
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
        socket_sendto($this->getSocket(), $message, strlen($message), 0, $this->host, $this->port);
    } 
}
