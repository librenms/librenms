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
    public function mark($name, array $values = [], $timePrecision = false)
    {
        $data =[];

        $timePrecision = $this->clearTimePrecision($timePrecision);

        $data["database"] = $this->getAdapter()->getOptions()->getDatabase();
        $data['points'][0]['name'] = $name;
        $data['points'][0]['fields'] = $values;

        return $this->getAdapter()->send($data, $timePrecision);
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
