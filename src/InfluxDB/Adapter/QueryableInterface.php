<?php
namespace InfluxDb\Adapter;

interface QueryableInterface
{
    public function query($query, $timePrecision = false);
}
