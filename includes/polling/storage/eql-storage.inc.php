<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2016 Peter TKATCHENKO https://github.com/Peter2121/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (! is_array($storage_cache1['eql-storage'])) {
    $storage_cache1['eql-storage'] = snmpwalk_cache_oid($device, 'EqliscsiVolumeEntry', null, 'EQLVOLUME-MIB', 'equallogic');
    d_echo($storage_cache1);
}

if (! is_array($storage_cache2['eql-storage'])) {
    $storage_cache2['eql-storage'] = snmpwalk_cache_oid($device, 'EqliscsiVolumeStatusEntry', null, 'EQLVOLUME-MIB', 'equallogic');
    d_echo($storage_cache2);
}

$iind = 0;
$storage_cache10 = [];
$storage_cache20 = [];

d_echo($storage);

foreach ($storage_cache1['eql-storage'] as $index => $ventry) {
    if (! array_key_exists('eqliscsiVolumeName', $ventry)) {
        continue;
    }
    if (is_int($index)) {
        $iind = $index;
    } else {
        $arrindex = explode('.', $index);
        $iind = (int) cast_number(end($arrindex));
    }
    if (is_int($iind)) {
        $storage_cache10[$iind] = $ventry;
    }
}
d_echo($storage_cache10);

foreach ($storage_cache2['eql-storage'] as $index => $vsentry) {
    if (! array_key_exists('eqliscsiVolumeStatusAvailable', $vsentry)) {
        continue;
    }
    if (is_int($index)) {
        $iind = $index;
    } else {
        $arrindex = explode('.', $index);
        $iind = (int) cast_number(end($arrindex));
    }
    if (is_int($iind)) {
        $storage_cache20[$iind] = $vsentry;
    }
}
d_echo($storage_cache20);

$entry1 = $storage_cache10[$storage['storage_index']];
$entry2 = $storage_cache20[$storage['storage_index']];

$storage['units'] = 1000000;
$storage['size'] = ($entry1['eqliscsiVolumeSize'] * $storage['units']);
$storage['used'] = ($entry2['eqliscsiVolumeStatusAllocatedSpace'] * $storage['units']);
$storage['free'] = ($storage['size'] - $storage['used']);
