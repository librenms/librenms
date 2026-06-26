<?php

use Illuminate\Support\Arr;

if ($sensor['sensor_type'] === 'batteryTestQuality') {
    $raw = SnmpQuery::walk($sensor['sensor_oid'])->values();
    $sensor_value = Arr::last($raw);
    unset($raw);
}
