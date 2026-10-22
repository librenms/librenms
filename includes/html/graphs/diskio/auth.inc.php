<?php

if (is_numeric($vars['id'])) {
    $disk = dbFetchRow('SELECT * FROM `ucd_diskio` AS U, `devices` AS D WHERE U.diskio_id = ? AND U.device_id = D.device_id', [$vars['id']]);

    if (is_numeric($disk['device_id']) && ($auth || device_permitted($disk['device_id']))) {
        $device = device_by_id_cache($disk['device_id']);

        $rrd_filename = Rrd::name($disk['hostname'], ['ucd_diskio', $disk['diskio_descr']]);

        $title = ' :: Disk :: ' . $disk['diskio_descr'];
        $auth = true;
    }
}
