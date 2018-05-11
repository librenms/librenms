<?php

/*Wireless support for
AT-TQ2403, AT-TQ2403EX, AT-TQ2450, AT-TQ3600,
AT-TQ3200, AT-TQ3400, AT-TQ4400, AT-TQ4600, AT-TQ4400e

Use sysDescr to get Hardware, SW version*/

list($a,$b,$c) = explode(' ', $device['sysDescr']);
    $hardware = $a;
    $version = $c;

$data_array = array();
$data_array = snmpwalk_cache_multi_oid($device, '.1.3.6.1.4.1.207', $data_array);
