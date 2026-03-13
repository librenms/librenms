<?php

use LibreNMS\Enum\Sensor as SensorEnum;

for ($i = 1; $i <= 3; $i++) {
    $current_oid = ".1.3.6.1.4.1.6050.5.4.1.1.3.$i";
    $descr = "Input Phase $i";
    $current = SnmpQuery::get($current_oid)->value();
    $type = 'gamatronicups';
    $precision = 1;
    $index = $i;
    $lowlimit = 0;
    $warnlimit = null;
    $limit = null;

    discover_sensor(null, SensorEnum::Current, $device, $current_oid, $index, $type, $descr, '1', '1', $lowlimit, null, null, null, $current);
}

for ($i = 1; $i <= 3; $i++) {
    $current_oid = ".1.3.6.1.4.1.6050.5.5.1.1.3.$i";
    $descr = "Output Phase $i";
    $current = SnmpQuery::get($current_oid)->value();
    $type = 'gamatronicups';
    $precision = 1;
    $index = (100 + $i);
    $lowlimit = 0;
    $warnlimit = null;
    $limit = null;

    discover_sensor(null, SensorEnum::Current, $device, $current_oid, $index, $type, $descr, '1', '1', $lowlimit, null, null, null, $current);
}
