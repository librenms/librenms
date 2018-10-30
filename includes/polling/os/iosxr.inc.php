<?php

if (preg_match('/^Cisco IOS XR Software \(Cisco ([^\)]+)\),\s+Version ([^\[]+)\[([^\]]+)\]/', $device['sysDescr'], $regexp_result)) {
    $hardware = $regexp_result[1];
    $features = $regexp_result[3];
    $version  = $regexp_result[2];
} else {
    // It is not an IOS-XR ... What should we do ?
}

$serial = get_main_serial($device);

echo "\n".$device['sysDescr']."\n";
