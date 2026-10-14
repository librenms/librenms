<?php

if ($auth || device_permitted($device['device_id'])) {
    $graph_title = DeviceCache::get($device['device_id'])->displayName();
    $auth = true;
}
