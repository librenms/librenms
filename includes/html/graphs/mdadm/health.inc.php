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
    ->where('sensor_type', 'mdadm_array_health_status')
    ->where('group', 'Mdadm ' . $array)
    ->first();

if ($sensor === null) {
    throw new RrdGraphException('No health sensor for array: ' . $array);
}

$rrd_filename = Rrd::name($device['hostname'], ['sensor', $sensor->sensor_class, $sensor->sensor_type, $sensor->sensor_index]);

require 'includes/html/graphs/sensor/generic.inc.php';

$rrd_options[] = 'COMMENT:\n';
$rrd_options[] = 'COMMENT:Health\:  0=Healthy  1=Degraded  2=Failed Devices  3=Missing Device  4=Clear\l';
$rrd_options[] = 'COMMENT:          5=Inactive  6=Suspended  7=Readonly  8=Read Auto  9=Write Pending  -1=Unknown\l';
