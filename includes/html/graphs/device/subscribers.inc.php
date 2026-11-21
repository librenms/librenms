<?php

use App\Models\SubscriberCount;

// session types of the device, the total only when the device does not report session types
$types = SubscriberCount::where('device_id', $device['device_id'])->whereNull('port_id')->pluck('session_type');
if ($types->count() > 1) {
    $types = $types->reject(fn ($type) => $type === 'total');
}

$rrd_list = [];
foreach (array_intersect(array_keys(SubscriberCount::SESSION_TYPES), $types->all()) as $type) {
    $rrd_filename = Rrd::name($device['hostname'], ['subscribers', $type]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => SubscriberCount::SESSION_TYPES[$type],
            'ds' => 'sessions',
        ];
    }
}

$unit_text = 'Sessions';
$units = '';
$total_units = '';
$colours = 'mixed';
$scale_min = '0';
$nototal = count($rrd_list) < 2;

if ($rrd_list) {
    include 'includes/html/graphs/generic_multi_line.inc.php';
}
