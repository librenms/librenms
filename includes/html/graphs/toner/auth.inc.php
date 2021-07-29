<?php

if (is_numeric($vars['id'])) {
    $toner = dbFetchRow('SELECT * FROM `printer_supplies` WHERE `supply_id` = ?', [$vars['id']]);

    if (is_numeric($toner['device_id']) && ($auth || device_permitted($toner['device_id']))) {
        $device = device_by_id_cache($toner['device_id']);
        $rrd_filename = Rrd::name($device['hostname'], ['toner', $toner['supply_type'], $toner['supply_index']]);

        $title = generate_device_link($device);
        $title .= ' :: Toner :: ' . htmlentities($toner['supply_descr']);
        $auth = true;
    }
}
