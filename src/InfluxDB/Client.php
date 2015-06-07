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

    public function mark($name, array $values = [], $timePrecision = false)
    {
        $data = $name;
        if (!is_array($name)) {
            $data =[];

            $timePrecision = $this->clearTimePrecision($timePrecision);

            $data["database"] = $this->getAdapter()->getOptions()->getDatabase();
            $data['points'][0]['name'] = $name;
            $data['points'][0]['fields'] = $values;
        }

        return $this->getAdapter()->send($data, $timePrecision);
    }

    public function query($query, $timePrecision = false)
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }

        $timePrecision = $this->clearTimePrecision($timePrecision);

        $return = $this->getAdapter()->query($query, $timePrecision);

        return $return;
    }

    public function getDatabases()
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }
        return $this->getAdapter()->getDatabases();
    }

    public function createDatabase($name)
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }
        return $this->getAdapter()->createDatabase($name);
    }

    public function deleteDatabase($name)
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }
        return $this->getAdapter()->deleteDatabase($name);
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
