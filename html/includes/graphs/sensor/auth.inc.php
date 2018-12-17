<?php

if (is_numeric($vars['id'])) {
    $sensor = dbFetchRow('SELECT * FROM sensors WHERE sensor_id = ?', array($vars['id']));

    if (is_numeric($sensor['device_id']) && ($auth || device_permitted($sensor['device_id']))) {
        $device = device_by_id_cache($sensor['device_id']);

        $rrd_filename = get_sensor_rrd($device, $sensor);

        $title  = generate_device_link($device);
        $title .= ' :: Sensor :: '.htmlentities($sensor['sensor_descr']);
        $auth   = true;
    }
} elseif ($vars['type'] == 'sensor_clients' && strpos($vars['id'], ',') !== false) {
    foreach (explode(',', $vars['id']) as $id) {
        $id = str_replace('!', '', $id);
        $sensor = dbFetchRow('SELECT * FROM sensors WHERE sensor_id = ?', array($id));
        if (is_numeric($sensor['device_id']) && ($auth || device_permitted($sensor['device_id']))) {
            $sens_auth[] = true;
        }
    }
    
    $is_all_good=true;
    foreach ($sens_auth as $check) {
        if (!$check) {
            $is_all_good=false;
        }
    }

    if ($is_all_good) {
        $auth   = true;
    }
}
