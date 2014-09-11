<?php
namespace InfluxDb\Adapter;

interface ConnectableInterface
{
    public function connect();
    public function disconnect();
}
