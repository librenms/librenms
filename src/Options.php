<?php

namespace InfluxDB;

/**
 * Manage in the best way InfluxDB Client Configuration
 */
class Options
{
    private $host;

    private $port;

    private $username;

    private $password;

    private $protocol;

    private $database;

    private $retentionPolicy;

    private $tags;

    public function __construct()
    {
        $this->setHost("localhost");
        $this->setPort(8086);
        $this->setUsername("root");
        $this->setPassword("root");
        $this->setProtocol("http");

        $this->setRetentionPolicy("default");
        $this->setTags([]);
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
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
}
