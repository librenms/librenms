<?php

if (is_numeric($vars['device']) && ($auth || device_permitted($vars['src']))) {
    $device = device_by_id_cache($vars['device']);
    $graph_title = DeviceCache::get($device['device_id'])->displayName();
    $auth = true;
}
