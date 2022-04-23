<?php

if (is_numeric($vars['id'])) {
    $sensor = dbFetchRow('SELECT * FROM `wireless_sensors` WHERE `sensor_id` = ?', [$vars['id']]);

    if (is_numeric($sensor['device_id']) && ($auth || device_permitted($sensor['device_id']))) {
        $device = device_by_id_cache($sensor['device_id']);

        $rrd_filename = Rrd::name($device['hostname'], ['wireless-sensor', $sensor['sensor_class'], $sensor['sensor_type'], $sensor['sensor_index']]);

        $title = generate_device_link($device);
        $title .= ' :: Wireless Sensor :: ' . htmlentities($sensor['sensor_descr']);
        $auth = true;
    }
}
