<?php

use App\Facades\Rrd;
use App\Models\MdadmArray;
use LibreNMS\Exceptions\RrdGraphException;

/**
 * Shared per-drive graph builder for the v3 mdadm app: one line per member
 * device. Type files set $unit_text and $ds before requiring this.
 *
 * Reads the per-drive RRD keyed by the stable array UUID and the member's
 * superblock device UUID (dev_id fallback):
 * ['app', 'mdadm', <app_id>, <array uuid>, <dev uuid|dev_id>].
 */
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

$graph_title = $dbArray->graphLabel() . ' :: Drive ' . $unit_text;

$name = 'mdadm';
$unitlen = 12;
$bigdescrlen = 12;
$smalldescrlen = 12;
$colours = 'mixed';
$dostack = 0;
$printtotal = 0;
$scale_min = 0;

$rrd_list = [];
foreach ($dbArray->drives as $drive) {
    $devId = (string) ($drive->dev_id ?? '');
    if ($devId === '') {
        continue;
    }

    // Match the poll-side key: stable superblock device UUID, else dev_id.
    $driveKey = $drive->dev_uuid !== null && $drive->dev_uuid !== '' ? (string) $drive->dev_uuid : $devId;
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $dbArray->uuid . '_' . $driveKey]);
    if (! Rrd::checkRrdExists($rrd_filename)) {
        continue;
    }

    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => $drive->path !== null && $drive->path !== '' ? basename((string) $drive->path) : $devId,
        'ds' => $ds,
    ];
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
