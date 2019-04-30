<?php
// FIXME - this could do with some performance improvements, i think. possible rearranging some tables and setting flags at poller time (nothing changes outside of then anyways)

use LibreNMS\ObjectCache;


$ports = new ObjectCache('ports');

// FIXME does not check user permissions...
foreach (dbFetchRows("SELECT sensor_class,COUNT(sensor_id) AS c FROM sensors GROUP BY sensor_class ORDER BY sensor_class ") as $row) {
    $used_sensors[$row['sensor_class']] = $row['c'];
}

$toner = new ObjectCache('toner');


$alerts = new ObjectCache('alerts');

$notifications = new ObjectCache('notifications');
