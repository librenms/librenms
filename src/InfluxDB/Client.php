<?php

namespace InfluxDB;

use InfluxDb\Adapter\QueryableInterface;
use InfluxDB\Filter\FilterInterface;

class Client
{
    private $adapter;
    private $filter;

    public function setFilter(Filter\FilterInterface $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setAdapter(Adapter\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function mark($name, array $values, $timePrecision = false)
    {
        $data =[];

        $timePrecision = $this->clearTimePrecision($timePrecision);

        $data['name'] = $name;
        $data['columns'] = array_keys($values);
        $data['points'][] = array_values($values);

        return $this->getAdapter()->send([$data], $timePrecision);
    }

    public function query($query, $timePrecision = false)
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }

        $timePrecision = $this->clearTimePrecision($timePrecision);

        $return = $this->getAdapter()->query($query, $timePrecision);

        if ($this->getFilter() instanceOf FilterInterface) {
            $return = $this->getFilter()->filter($return);
        }

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
