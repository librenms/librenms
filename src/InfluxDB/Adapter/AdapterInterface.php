<?php
namespace InfluxDB\Adapter;

interface AdapterInterface
{
    public function send($message, $timePrecision = false);
}
