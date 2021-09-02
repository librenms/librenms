<?php

if (! is_array($storage_cache['dsk'])) {
    $storage_cache['dsk'] = snmpwalk_cache_oid($device, 'dskTable', null, 'UCD-SNMP-MIB');
    d_echo($storage_cache);
}

$entry = $storage_cache['dsk'][$storage['storage_index']];

$storage['units'] = 1024;
$storage['size'] = ($entry['dskTotal'] * $storage['units']);
$storage['free'] = ($entry['dskAvail'] * $storage['units']);
$storage['used'] = ($storage['size'] - $storage['free']);
