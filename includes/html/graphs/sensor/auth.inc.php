<?php

use App\Facades\Rrd;
use App\Models\Sensor;

if (is_numeric($vars['id'])) {
    $sensor = Sensor::find($vars['id']);

    if ($auth || device_permitted($sensor->device_id)) {
        $device = device_by_id_cache($sensor->device_id);

        $rrd_filename = Rrd::name($device['hostname'], get_sensor_rrd_name($device, $sensor));

        $title = generate_device_link($device);
        $title .= ' :: Sensor :: ' . htmlentities((string) $sensor->sensor_descr);
        $auth = true;
    }
}
