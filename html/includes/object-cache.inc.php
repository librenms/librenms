<?php

// FIXME queries such as the one below should probably go into index.php?
// FIXME: This appears to keep a complete cache of device details in memory for every page load.
// It would be interesting to know where this is used.  It probably should have its own API.
use LibreNMS\ObjectCache;

foreach (dbFetchRows('SELECT * FROM `devices` ORDER BY `hostname`') as $device) {
    $cache['devices']['hostname'][$device['hostname']] = $device['device_id'];
    $cache['devices']['id'][$device['device_id']]      = $device;

    $cache['device_types'][$device['type']]++;
}

$devices  = new ObjectCache('devices');
$ports    = new ObjectCache('ports');
$services = new ObjectCache('services');

if ($devices['down']) {
    $devices['bgcolour'] = '#ffcccc';
} else {
    $devices['bgcolour'] = 'transparent';
}

if ($ports['down']) {
    $ports['bgcolour'] = '#ffcccc';
} else {
    $ports['bgcolour'] = '#e5e5e5';
}

if ($services['down']) {
    $services['bgcolour'] = '#ffcccc';
} else {
    $services['bgcolour'] = 'transparent';
}
