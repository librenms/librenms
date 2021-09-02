<?php

if (is_numeric($vars['id'])) {
    $storage = dbFetchRow('SELECT * FROM `storage` WHERE `storage_id` = ?', [$vars['id']]);

    if (is_numeric($storage['device_id']) && ($auth || device_permitted($storage['device_id']))) {
        $device = device_by_id_cache($storage['device_id']);
        $rrd_filename = Rrd::name($device['hostname'], ['storage', $storage['storage_mib'], $storage['storage_descr']]);

        $title = generate_device_link($device);
        $title .= ' :: Storage :: ' . htmlentities($storage['storage_descr']);
        $auth = true;
    }
}
