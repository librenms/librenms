<?php

foreach (dbFetchRows('SELECT * FROM `devices`,`locations` WHERE location_id = ? && devices.location_id = locations.id', [$vars['id']]) as $device) {
    if ($auth || device_permitted($device['device_id'])) {
        $devices[] = $device;
        $title = $device['location'];
        $auth = true;
    }
}
