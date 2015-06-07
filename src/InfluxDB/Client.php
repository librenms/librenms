<?php

namespace InfluxDB;

use InfluxDb\Adapter\QueryableInterface;
use InfluxDB\Filter\FilterInterface;

/**
 * Client to manage request at InfluxDB
 */
class Client
{
    private $adapter;

    public function setAdapter(Adapter\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function mark($name, array $values = [])
    {
        $data = $name;
        if (!is_array($name)) {
            $data =[];

            $data["database"] = $this->getAdapter()->getOptions()->getDatabase();
            $data['points'][0]['name'] = $name;
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
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }
        return $this->getAdapter()->query("show databases");
    }

    public function createDatabase($name)
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }
        return $this->getAdapter()->query("create database \"{$name}\"");
    }

    public function deleteDatabase($name)
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }
        return $this->getAdapter()->query("drop database \"{$name}\"");
    }

    private function clearTimePrecision($timePrecision)
    {
        switch ($timePrecision) {
            case 's':
            case 'u':
            case 'ms':
                break;
            default:
                $timePrecision = false;
        }

        return $timePrecision;
    }
}
