#!/usr/bin/php

<?php

require __DIR__ . '/vendor/autoload.php';

$host = 'localhost';
$port = 8086;

$client = new \Leaseweb\InfluxDB\Client($host, $port);