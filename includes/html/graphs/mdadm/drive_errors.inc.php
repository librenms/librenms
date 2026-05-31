<?php

use App\Facades\Rrd;
use App\Models\MdadmArray;
use App\Models\Sensor;
use LibreNMS\Exceptions\RrdGraphException;

require 'includes/html/graphs/common.inc.php';

$arrayParam = $vars['array'] ?? '';
if ($arrayParam === '') {
    throw new RrdGraphException('No array selected');
}

$dbArray = MdadmArray::where('app_id', $app->app_id)
    ->where(function ($q) use ($arrayParam): void {
        $q->where('uuid', $arrayParam)->orWhere('array_name', $arrayParam)->orWhere('md_id', $arrayParam);
    })
    ->with('drives')
    ->first();

if ($dbArray === null) {
    throw new RrdGraphException('Unknown array: ' . $arrayParam);
}

// Sensor group uses md_id (the kernel device name, matching Common.php: $array['name'] ?? $uuid).
$arrayName = trim((string) ($dbArray->md_id ?? '')) ?: (string) $dbArray->uuid;
$sensorGroup = "Mdadm {$arrayName}::devices";
$graph_title = $dbArray->graphLabel() . ' :: Drive Errors';

$sensors = Sensor::where('device_id', $device['device_id'])
    ->where('sensor_type', 'mdadm_device_error')
    ->where('group', $sensorGroup)
    ->get()
    ->keyBy('sensor_index');

if ($sensors->isEmpty()) {
    throw new RrdGraphException('No error sensors for array: ' . $arrayParam);
}

$sensor_color = session('applied_site_style') == 'dark' ? '#f2f2f2' : '#272b30';
$background_color = session('applied_site_style') == 'dark' ? '#272b30' : '#ffffff';
$colours_list = ['CC0000', '008C00', '4096EE', '73880A', 'D01F3C', '36393D', 'FF0084'];

$graph_params->vertical_label = 'errors';
$graph_params->scale_min = 0;

$rrd_options[] = 'COMMENT:' . Rrd::fixedSafeDescr('', 14) . '       Now         Avg        Min        Max\n';

$colourIdx = 0;
$firstSensor = null;
foreach ($dbArray->drives->sortBy('slot') as $drive) {
    $idx = $dbArray->uuid . '_' . $drive->dev_id . '_errors';
    $sensor = $sensors->get($idx);
    if ($sensor === null) {
        continue;
    }

    $rrd_filename = Rrd::name($device['hostname'], ['sensor', $sensor->sensor_class, $sensor->sensor_type, $sensor->sensor_index]);
    if (! Rrd::checkRrdExists($rrd_filename)) {
        continue;
    }

    $firstSensor ??= $sensor;

    $label = $drive->path !== null && $drive->path !== '' ? basename((string) $drive->path) : $drive->dev_id;
    $descrFixed = Rrd::fixedSafeDescr($label, 12);
    $colour = $colours_list[$colourIdx++ % count($colours_list)];
    $field = 'err' . $sensor->sensor_id;

    $rrd_options[] = 'DEF:' . $field . '=' . $rrd_filename . ':sensor:AVERAGE';
    $rrd_options[] = 'DEF:' . $field . 'max=' . $rrd_filename . ':sensor:MAX';
    $rrd_options[] = 'DEF:' . $field . 'min=' . $rrd_filename . ':sensor:MIN';
    $rrd_options[] = 'LINE2:' . $field . '#' . $colour . ':' . $descrFixed;
    $rrd_options[] = 'GPRINT:' . $field . ':LAST:%7.1lf%S';
    $rrd_options[] = 'GPRINT:' . $field . ':AVERAGE:%7.1lf%S';
    $rrd_options[] = 'GPRINT:' . $field . ':MIN:%7.1lf%S';
    $rrd_options[] = 'GPRINT:' . $field . ':MAX:%7.1lf%S\\l';
}

// Mirror sensor/generic.inc.php threshold rendering using the first sensor's limits.
// All drives share the same discovery-set thresholds.
if ($firstSensor !== null) {
    if ($firstSensor->hasThresholds()) {
        $rrd_options[] = 'COMMENT:Alert thresholds\:';
        if ($firstSensor->sensor_limit_low !== null) {
            $rrd_options[] = 'LINE1.5:' . $firstSensor->sensor_limit_low . '#00008b:low = ' . $firstSensor->formatValue('sensor_limit_low') . ':dashes';
        }
        if ($firstSensor->sensor_limit_low_warn !== null) {
            $rrd_options[] = 'LINE1.5:' . $firstSensor->sensor_limit_low_warn . '#005bdf:low_warn = ' . $firstSensor->formatValue('sensor_limit_low_warn') . ':dashes';
        }
        if ($firstSensor->sensor_limit_warn !== null) {
            $rrd_options[] = 'LINE1.5:' . $firstSensor->sensor_limit_warn . '#ffa420:high_warn = ' . $firstSensor->formatValue('sensor_limit_warn') . ':dashes';
        }
        if ($firstSensor->sensor_limit !== null) {
            $rrd_options[] = 'LINE1.5:' . $firstSensor->sensor_limit . '#ff0000:high = ' . $firstSensor->formatValue('sensor_limit') . ':dashes';
        }
    } else {
        // No thresholds — force rrdtool to scale to data range (mirrors sensor/generic.inc.php).
        $firstField = 'err' . $firstSensor->sensor_id;
        $rrd_options[] = 'CDEF:canvas_max=' . $firstField . 'max,1.01,*';
        $rrd_options[] = 'LINE1:canvas_max#00000000::dashes';
        $rrd_options[] = 'CDEF:canvas_min=' . $firstField . 'min,0.99,*';
        $rrd_options[] = 'LINE1:canvas_min#00000000::dashes';
    }
}
