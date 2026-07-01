<?php

use App\Facades\Rrd;
use App\Models\MdadmArray;

$name = 'mdadm';
$unit_text = 'State';
$unitlen = 8;
$bigdescrlen = 10;
$smalldescrlen = 10;
$colours = 'mixed';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;
$scale_max = 12;

$arrayParam = $vars['array'] ?? '';

$query = MdadmArray::where('app_id', $app->app_id);
if ($arrayParam !== '') {
    $query->where(function ($q) use ($arrayParam): void {
        $q->where('uuid', $arrayParam)->orWhere('array_name', $arrayParam)->orWhere('md_id', $arrayParam);
    });
}

$rrd_list = [];

foreach ($query->get() as $dbArray) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, $dbArray->uuid]);
    if (! Rrd::checkRrdExists($rrd_filename)) {
        continue;
    }

    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => $dbArray->graphLabel(),
        'ds' => 'state',
    ];
}

if (empty($rrd_list)) {
    return;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';

$rrd_options[] = 'COMMENT:\n';
$rrd_options[] = 'COMMENT:States\:  0=unknown  1=clear  2=inactive  3=suspended  4=readonly  5=read-auto\l';
$rrd_options[] = 'COMMENT:          6=clean  7=active  8=write-pending  9=active-idle  10=degraded  11=failed\l';
