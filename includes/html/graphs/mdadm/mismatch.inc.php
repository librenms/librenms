<?php

use App\Facades\Rrd;
use App\Models\Sensor;
use LibreNMS\Exceptions\RrdGraphException;

require 'includes/html/graphs/common.inc.php';

$array = $vars['array'] ?? '';
if ($array === '') {
    throw new RrdGraphException('No array selected');
}

$sensor = Sensor::where('device_id', $device['device_id'])
    ->where('sensor_type', 'mdadm_array_mismatch')
    ->where('group', 'Mdadm ' . $array)
    ->first();

if ($sensor === null) {
    throw new RrdGraphException('No mismatch sensor for array: ' . $array);
}

$rrd_filename = Rrd::name($device['hostname'], ['sensor', $sensor->sensor_class, $sensor->sensor_type, $sensor->sensor_index]);

require 'includes/html/graphs/sensor/generic.inc.php';
