<?php
namespace InfluxDB;

interface ClientInterface
{
    public function getAdapter();
    public function setAdapter(Adapter\AdapterInterface $adapter);
    public function connect();
    public function disconnect();
    public function mark($name, array $values);
}
