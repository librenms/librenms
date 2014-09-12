<?php

namespace InfluxDB;

use InfluxDb\Adapter\QueryableInterface;
use InfluxDb\Adapter\ConnectableInterface;

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

    public function mark($name, array $values)
    {
        $data =[];
        $data['name'] = $name;
        $data['columns'] = array_keys($values);
        $data['points'][] = array_values($values);

        return $this->getAdapter()->send([$data]);
    }

    public function query($query, $timePrecision = false)
    {
        if (!($this->getAdapter() instanceOf QueryableInterface)) {
            throw new  \BadMethodCallException("You can query the database only if the adapter supports it!");
        }

        $timePrecision = $this->clearTimePrecision($timePrecision);

        return $this->getAdapter()->query($query, $timePrecision);
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
