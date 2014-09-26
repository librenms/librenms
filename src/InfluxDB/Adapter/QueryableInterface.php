<?php
namespace InfluxDB\Adapter;

interface QueryableInterface
{
    public function query($query, $timePrecision = false);
    public function getDatabases();
    public function createDatabase($name);
    public function deleteDatabase($name);
}
