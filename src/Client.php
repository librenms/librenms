<?php

namespace InfluxDB;

use InfluxDB\Adapter\WritableInterface;
use InfluxDb\Adapter\QueryableInterface;

/**
 * Client to manage request at InfluxDB
 */
class Client
{
    private $adapter;

    public function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function mark($name, array $values = [])
    {
        if (!($this->getAdapter() instanceOf WritableInterface)) {
            throw new  \BadMethodCallException("You can write data to database only if the adapter supports it!");
        }

        $data = $name;
        if (!is_array($name)) {
            $data =[];

            $data['points'][0]['measurement'] = $name;
            $data['points'][0]['fields'] = $values;
        }

        return $this->getAdapter()->send($data);
    }

    public function query($query)
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }

        $return = $this->getAdapter()->query($query);

        return $return;
    }

    public function getDatabases()
    {
        return $this->getAdapter()->query("show databases");
    }

    public function createDatabase($name)
    {
        return $this->getAdapter()->query("create database \"{$name}\"");
    }

    public function deleteDatabase($name)
    {
        return $this->getAdapter()->query("drop database \"{$name}\"");
    }
}
