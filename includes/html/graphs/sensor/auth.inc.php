<?php

use App\Facades\Rrd;
use App\Models\Sensor;

if (is_numeric($vars['id'])) {
    $sensor = Sensor::find($vars['id']);

    if ($auth || device_permitted($sensor->device_id)) {
        $device = device_by_id_cache($sensor->device_id);

        $rrd_filename = Rrd::name($device['hostname'], $sensor->getRrdName());

        $title = ' :: Sensor :: ' . $sensor->sensor_descr;
        $auth = true;
    }
}
