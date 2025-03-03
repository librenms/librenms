<?php

$proc = dbFetchRow('SELECT * FROM `processors` where `processor_id` = ?', [$vars['id']]);

if (is_numeric($proc['device_id']) && ($auth || device_permitted($proc['device_id']))) {
    $device = device_by_id_cache($proc['device_id']);
    $rrd_filename = Rrd::name($device['hostname'], ['processor', $proc['processor_type'], $proc['processor_index']]);
    $title = generate_device_link($device);
    $title .= ' :: Processor :: ' . htmlentities($proc['processor_descr']);
    $auth = true;
}
