<?php

use Illuminate\Support\Facades\Auth;

foreach (dbFetchRows('SELECT * FROM `devices`,`locations` WHERE location_id = ? && devices.location_id = locations.id', [$vars['id']]) as $device) {
    if ($auth || Auth::user()->canAccessDevice($device['device_id'])) {
        $devices[] = $device;
        $title = $device['location'];
        $auth = true;
    }
}
