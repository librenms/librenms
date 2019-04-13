<?php

if (is_numeric($vars['id'])) {
    $toner = dbFetchRow('SELECT * FROM `toner` WHERE `toner_id` = ?', array($vars['id']));

    if (is_numeric($toner['device_id']) && ($auth || device_permitted($toner['device_id']))) {
        $device       = device_by_id_cache($toner['device_id']);
        $rrd_filename = rrd_name($device['hostname'], array('toner', $toner['toner_index']));

        $title  = generate_device_link($device);
        $title .= ' :: Toner :: '.htmlentities($toner['toner_descr']);
        $auth   = true;
    }
}
