<?php
namespace InfluxDB\Adapter;

interface QueryableInterface
{
    public function query($query);
}
