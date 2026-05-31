<?php

use App\Facades\Rrd;
use App\Models\Sensor;
use LibreNMS\Exceptions\RrdGraphException;

require 'includes/html/graphs/common.inc.php';

if (! isset($vars['sensor_id']) || ! is_numeric($vars['sensor_id'])) {
    throw new RrdGraphException('No sensor_id provided');
}

$sensor = Sensor::find((int) $vars['sensor_id']);

if ($sensor === null || (int) $sensor->device_id !== (int) $device['device_id']) {
    throw new RrdGraphException('Device health sensor not found');
}

$rrd_filename = Rrd::name($device['hostname'], ['sensor', $sensor->sensor_class, $sensor->sensor_type, $sensor->sensor_index]);

require 'includes/html/graphs/sensor/generic.inc.php';
