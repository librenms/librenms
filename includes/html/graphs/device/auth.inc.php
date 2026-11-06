<?php

if ($auth || device_permitted($device['device_id'])) {
    $device1 = DeviceCache::get($device['device_id']);
    $graph_title = $device1->display;
    $auth = true;
}
