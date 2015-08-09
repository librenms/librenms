<?php
namespace InfluxDB\Adapter;

interface WritableInterface
{
    public function send(array $message);
}
