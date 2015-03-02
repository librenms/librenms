<?php

namespace InfluxDB;

use InfluxDb\Adapter\QueryableInterface;
use InfluxDB\Filter\FilterInterface;

/**
 * Client to manage request at InfluxDB
 */
class Client
{
    /**
     * @var \InfluxDB\Adapter\AdapterInterface
     */
    private $adapter;

    /**
     * @var \InfluxDB\Filter\FilterInterface
     */
    private $filter;

    /**
     * Set filter
     * @param Filter\FilterInterface $filter
     * @return Client
     */
    public function setFilter(Filter\FilterInterface $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Get filter
     * @return Filter\FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set InfluxDB adapter
     * @param Adapter\AdapterInterface
     * @return Client
     */
    public function setAdapter(Adapter\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Get adapter
     * @return Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Insert point into series
     * @param string $name
     * @param array $value
     * @param bool|string $timePrecision
     * @return mixed
     */
    public function mark($name, array $values, $timePrecision = false)
    {
        $data =[];

        $timePrecision = $this->clearTimePrecision($timePrecision);

        $data['name'] = $name;
        $data['columns'] = array_keys($values);
        $data['points'][] = array_values($values);

        return $this->getAdapter()->send([$data], $timePrecision);
    }

    /**
     * Make a query into database
     * @param string $query
     * @param bool|string $timePrecision
     * @return array
     */
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

    /**
     * List of databases
     * @return array
     */
    public function getDatabases()
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }
        return $this->getAdapter()->getDatabases();
    }

    /**
     * Create database by name
     * @param string $name
     */
    public function createDatabase($name)
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }
        return $this->getAdapter()->createDatabase($name);
    }

    /**
     * Delete database by name
     * @param string $name
     */
    public function deleteDatabase($name)
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }
        return $this->getAdapter()->deleteDatabase($name);
    }

    /**
     * List of time precision choose
     * @param string $timePrecision
     * @return bool|string
     */
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
