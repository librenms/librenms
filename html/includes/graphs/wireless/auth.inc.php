<?php

use App\Models\WirelessSensor;
use LibreNMS\Authentication\Auth;

if (is_numeric($vars['id'])) {
    /** @var WirelessSensor $sensor */
    $sensor = WirelessSensor::hasAccess(Auth::user())->find($vars['id']);
    if ($sensor) {
        $device = device_by_id_cache($sensor->device_id);
        $rrd_filename = $sensor->rrdName($device['hostname']);

        $title  = generate_device_link($device);
        $title .= ' :: Wireless Sensor :: '.htmlentities($sensor->description);
        $auth   = true;
    }
}
