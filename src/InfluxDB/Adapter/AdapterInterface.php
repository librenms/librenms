<?php
namespace InfluxDb\Adapter;

interface AdapterInterface 
{
    public function getName();

    public function connect();

    public function disconnect();

    public function send($message);
}
