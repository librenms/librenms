<?php

require 'includes/html/graphs/common.inc.php';

$unit_text ??= '';
$graph_params->scale_min = 0;
$graph_params->scale_max = 100;
$graph_params->base = 1024;

$iter = '1';

$colour = 'CC0000';
$colour_area = 'ffaaaa';

if ($width > '500') {
    $descr_len = 13;
} else {
    $descr_len = 8;
    $descr_len += round(($width - 250) / 8);
}

if ($width > '500') {
    $rrd_options[] = 'COMMENT:' . substr(str_pad($unit_text, $descr_len + 5), 0, $descr_len + 5) . ' Total       Used       Free     ( Min        Max        Ave     )';
    $rrd_options[] = "COMMENT:\l";
} else {
    $rrd_options[] = 'COMMENT:' . substr(str_pad($unit_text, $descr_len + 5), 0, $descr_len + 5) . " Total       Used       Free\l";
}

$descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr(short_hrDeviceDescr($mempool['mempool_descr']), $descr_len);

$amount_format = $mempool['mempool_total'] == 100 ? '%6.2lf%%' : '%6.2lf%sB';
$perc = round($mempool['mempool_perc'], 0);
$background = \LibreNMS\Util\Color::percentage($perc, $mempool['mempool_perc_warn']);

$rrd_options[] = "DEF:{$mempool['mempool_id']}used=$rrd_filename:used:AVERAGE";
$rrd_options[] = "DEF:{$mempool['mempool_id']}free=$rrd_filename:free:AVERAGE";
$rrd_options[] = "CDEF:{$mempool['mempool_id']}size={$mempool['mempool_id']}used,{$mempool['mempool_id']}free,+";
$rrd_options[] = "CDEF:{$mempool['mempool_id']}perc={$mempool['mempool_id']}used,{$mempool['mempool_id']}size,/,100,*";
$rrd_options[] = "CDEF:{$mempool['mempool_id']}percx=100,{$mempool['mempool_id']}perc,-";
$rrd_options[] = "AREA:{$mempool['mempool_id']}perc#" . $background['right'] . ':';

if ($width > '500') {
    $rrd_options[] = "LINE1.25:{$mempool['mempool_id']}perc#" . $background['left'] . ":$descr";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}size:LAST:$amount_format ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}used:LAST:$amount_format ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}free:LAST:$amount_format ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}free:MIN:$amount_format ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}free:MAX:$amount_format ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}free:AVERAGE:$amount_format\\n";
    $rrd_options[] = 'COMMENT:' . substr(str_pad('', $descr_len + 15), 0, $descr_len + 15);
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}perc:LAST:%5.2lf%%   ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}percx:LAST:%5.2lf%%   ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}perc:MIN:%5.2lf%%   ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}perc:MAX:%5.2lf%%   ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}perc:AVERAGE:%5.2lf%%\\n";
} else {
    $rrd_options[] = "LINE1.25:{$mempool['mempool_id']}perc#" . $background['left'] . ":$descr";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}size:LAST:$amount_format ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}used:LAST:$amount_format ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}free:LAST:$amount_format ";
    $rrd_options[] = "COMMENT:\l";
    $rrd_options[] = 'COMMENT:' . substr(str_pad('', $descr_len + 15), 0, $descr_len + 15);
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}perc:LAST:%6.2lf%% ";
    $rrd_options[] = "GPRINT:{$mempool['mempool_id']}percx:LAST:%6.2lf%% ";
    $rrd_options[] = "COMMENT:\l";
}//end if
