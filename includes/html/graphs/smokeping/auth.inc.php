<?php

use App\Facades\DeviceCache;

// the graph relates two devices: the graphed device and the source (in) or destination (out)
$peer_device_id = $vars['src'] ?? $vars['dest'] ?? null;

if (is_numeric($vars['device']) && is_numeric($peer_device_id)
    && ($auth || (device_permitted($vars['device']) && device_permitted($peer_device_id)))) {
    $device = DeviceCache::get($vars['device']);
    $graph_title = $device->displayName();
    $auth = true;
}
