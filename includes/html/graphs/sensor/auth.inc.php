<?php

if (is_numeric($vars['id'])) {
    $sensor = dbFetchRow('SELECT * FROM sensors WHERE sensor_id = ?', [$vars['id']]);

    if (is_numeric($sensor['device_id']) && ($auth || device_permitted($sensor['device_id']))) {
        $device = device_by_id_cache($sensor['device_id']);

        $rrd_filename = get_sensor_rrd($device, $sensor);

        $title = generate_device_link($device);
        $title .= ' :: Sensor :: ' . htmlentities($sensor['sensor_descr']);
        $auth = true;
    }
}
