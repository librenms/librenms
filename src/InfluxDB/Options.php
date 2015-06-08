<?php

namespace InfluxDB;

/**
 * Manage in the best way InfluxDB Client Configuration
 */
class Options
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string|int
     */
    private $port;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $database;

    private $retentionPolicy;

    /**
     * Set default options
     */
    public function __construct()
    {
        $this->setHost("localhost");
        $this->setPort(8086);
        $this->setUsername("root");
        $this->setPassword("root");
        $this->setProtocol("http");

        $this->setRetentionPolicy("default");
    }

    public function getRetentionPolicy()
    {
        return $this->retentionPolicy;
    }

    public function setRetentionPolicy($retentionPolicy)
    {
        $this->retentionPolicy = $retentionPolicy;
        return $this;
    }

    /**
     * @return string
     */
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

    public function getDatabase()
    {
        return $this->database;
    }

    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    public function getHttpSeriesEndpoint()
    {
        return sprintf(
            "%s://%s:%d/write",
            $this->getProtocol(),
            $this->getHost(),
            $this->getPort()
        );
    }

    public function getHttpQueryEndpoint($name = false)
    {
        $url = sprintf(
            "%s://%s:%d/query",
            $this->getProtocol(),
            $this->getHost(),
            $this->getPort()
        );

        return $url;
    }
}
