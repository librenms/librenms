<?php

$authorized_all = $auth; // guest auth (signed url, unauth graphs) covers all devices

foreach (dbFetchRows('SELECT * FROM `devices`,`locations` WHERE location_id = ? && devices.location_id = locations.id', [$vars['id']]) as $device) {
    if ($authorized_all || device_permitted($device['device_id'])) {
        $devices[] = $device;
        $title = $device['location'];
        $auth = true;
    }
}
