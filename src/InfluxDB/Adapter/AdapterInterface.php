<?php
namespace InfluxDb\Adapter;

interface AdapterInterface
{
    public function send($message);
}
