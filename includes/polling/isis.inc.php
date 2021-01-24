<?php

use App\Models\Ipv4Address;

use LibreNMS\RRD\RrdDefinition;

// Get device model
$device_model = DeviceCache::getPrimary();


// Poll ISIS adjacencies
$isis_adjs = snmpwalk_cache_oid($device, 'ISIS-MIB::isisISAdj', [], 'ISIS-MIB');

var_dump($isis_adjs);

echo PHP_EOL;

unset(
    $isis_adjs
);
