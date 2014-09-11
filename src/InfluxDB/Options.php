<?php

namespace InfluxDB;

class Options
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $protocol;

    public function __construct()
    {
        $this->host = "localhost";
        $this->port = 8086;
        $this->username = "root";
        $this->password = "root";
        $this->setProtocol("http");
    }

    public function getProtocol()
    {
        return $this->protocol;
    }

    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

    public function getHost()
    {
       return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getTcpEndpointFor($database)
    {
        return sprintf(
            "%s://%s:%d/db/%s/series?u=%s&p=%s",
            $this->getProtocol(),
            $this->getHost(),
            $this->getPort(),
            $database,
            $this->getUsername(),
            $this->getPassword()
        );
    }
}
