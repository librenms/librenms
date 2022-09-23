<?php

if ($device['os'] == 'ctm') {
    $validTypes = ['portOnM', 'portSyncM', 'portSyncS', 'portOnS'];
    if (in_array($sensor['sensor_type'], $validTypes)) {
        $octet = explode(',', $sensor_value);
        $sensor_value = $octet[$sensor['sensor_index'] - 1];
    }
    unset($validTypes, $octet);
}
