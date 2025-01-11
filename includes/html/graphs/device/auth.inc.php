<?php

use Illuminate\Support\Facades\Auth;

if ($auth || Auth::user()->canAccessDevice($device['device_id'])) {
    $title = generate_device_link($device);
    $graph_title = DeviceCache::get($device['device_id'])->displayName();
    $auth = true;
}
