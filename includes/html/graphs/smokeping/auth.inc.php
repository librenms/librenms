<?php

use App\Facades\DeviceCache;

if (is_numeric($vars['device']) && ($auth || device_permitted($vars['src']))) {
    $device = DeviceCache::get($vars['device']);
    $graph_title = $device->displayName();
    $auth = true;
}
