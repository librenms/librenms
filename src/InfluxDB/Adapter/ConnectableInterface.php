<?php
namespace InfluxDB\Adapter;

interface ConnectableInterface
{
    public function connect();
    public function disconnect();
}
