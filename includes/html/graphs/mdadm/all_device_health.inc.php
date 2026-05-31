<?php

use App\Facades\Rrd;
use App\Models\MdadmArray;
use App\Models\Sensor;
use LibreNMS\Exceptions\RrdGraphException;

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

$name = 'mdadm';
$unit_text = 'Health';
$unitlen = 8;
$bigdescrlen = 12;
$smalldescrlen = 12;
$colours = 'mixed';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;

$graph_title = $dbArray->graphLabel() . ' :: Drive Health';

$sensors = Sensor::where('device_id', $device['device_id'])
    ->where('sensor_type', 'mdadm_device_health_status')
    ->where('group', 'Mdadm ' . $dbArray->md_id . '::devices')
    ->get();

$drivesByDevId = $dbArray->drives->keyBy('dev_id');

$rrd_list = [];
foreach ($sensors as $sensor) {
    $rrd_filename = Rrd::name($device['hostname'], ['sensor', $sensor->sensor_class, $sensor->sensor_type, $sensor->sensor_index]);
    if (! Rrd::checkRrdExists($rrd_filename)) {
        continue;
    }

    // Sensor index format: {uuid}_{devId}_health — strip known prefix/suffix to get devId.
    $inner = substr((string) $sensor->sensor_index, strlen((string) $dbArray->uuid) + 1, -strlen('_health'));
    $devId = $inner !== '' && $inner !== false ? $inner : null;
    $drive = $devId !== null ? $drivesByDevId->get($devId) : null;
    $descr = $drive !== null && $drive->path !== null && $drive->path !== ''
        ? basename((string) $drive->path)
        : ($devId ?? $sensor->sensor_descr);

    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => $descr,
        'ds' => 'sensor',
    ];
}

if (empty($rrd_list)) {
    return;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';

$rrd_options[] = 'COMMENT:\n';
$rrd_options[] = 'COMMENT:Health\:  0=In Sync  1=Active  2=Write Mostly  3=Spare  4=Rebuilding\l';
$rrd_options[] = 'COMMENT:          5=Want Replacement  6=Replacement  7=Write Error  8=Blocked  9=Faulty  10=Missing\l';
