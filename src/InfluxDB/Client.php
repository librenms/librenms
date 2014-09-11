<?php

namespace InfluxDB;

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

    public function connect()
    {
        return $this->getAdapter()->connect();
    }

    public function disconnect()
    {
        return $this->getAdapter()->disconnect();
    }

    public function mark($name, array $values)
    {
        $data =[];
        $data['name'] = $name;
        $data['columns'] = array_keys($values);
        $data['points'][] = array_values($values);

        return $this->getAdapter()->send([$data]);
    }
}
