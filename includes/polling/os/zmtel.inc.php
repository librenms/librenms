<?php

$temp_data = snmpwalk_cache_oid($device, 'deviceInfo', [], 'ZMTEL-ODU-MIB', 'zmtel');

foreach ($temp_data as $data) {
    $version  = $data['softwareVersion'];
    $hardware = $data['modelName'] . ' ' . $data['hardwareVersion'];
    $serial   = $data['SN'];
    $features = 'bootROM ' . $data['bootROM'] . 'LTE Band ' . $data['lteBand'];
}
unset($temp_data);
