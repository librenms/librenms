<?php

$scale_min = '0';
$scale_max = '100';

require 'includes/html/graphs/common.inc.php';

$rrd_options .= ' -b 1024';

$iter = '1';

$rrd_options .= " COMMENT:'                        Size      Free   % Used\\n'";

$hostname = gethostbyid($storage['device_id']);

$colour = 'CC0000';
$colour_area = 'ffaaaa';

$descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($storage['storage_descr'], 16);

$percentage = round($storage['storage_perc'], 0);

$background = \LibreNMS\Util\Colors::percentage($percentage, $storage['storage_perc_warn']);

$rrd_options .= " DEF:used=$rrd_filename:used:AVERAGE";
$rrd_options .= " DEF:free=$rrd_filename:free:AVERAGE";
$rrd_options .= ' CDEF:size=used,free,+';
$rrd_options .= ' CDEF:perc=used,size,/,100,*';
$rrd_options .= ' AREA:perc#' . $background['right'] . ':';
$rrd_options .= ' LINE1.25:perc#' . $background['left'] . ":'$descr'";
$rrd_options .= ' GPRINT:size:LAST:%6.2lf%sB';
$rrd_options .= ' GPRINT:free:LAST:%6.2lf%sB';
$rrd_options .= ' GPRINT:perc:LAST:%5.2lf%%\\n';

if ($_GET['previous']) {
    $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr('Prev ' . $storage['storage_descr'], 16);

    $colour = '99999999';
    $colour_area = '66666666';

    $rrd_options .= " DEF:usedX=$rrd_filename:used:AVERAGE:start=" . $prev_from . ':end=' . $from;
    $rrd_options .= " DEF:freeX=$rrd_filename:free:AVERAGE:start=" . $prev_from . ':end=' . $from;
    $rrd_options .= " SHIFT:usedX:$period";
    $rrd_options .= " SHIFT:freeX:$period";
    $rrd_options .= ' CDEF:sizeX=usedX,freeX,+';
    $rrd_options .= ' CDEF:percX=usedX,sizeX,/,100,*';
    $rrd_options .= ' AREA:percX#' . $colour_area . ':';
    $rrd_options .= ' LINE1.25:percX#' . $colour . ":'$descr'";
    $rrd_options .= ' GPRINT:sizeX:LAST:%6.2lf%sB';
    $rrd_options .= ' GPRINT:freeX:LAST:%6.2lf%sB';
    $rrd_options .= ' GPRINT:percX:LAST:%5.2lf%%\\n';
}
