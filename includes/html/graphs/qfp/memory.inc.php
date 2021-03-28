<?php
/**
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 LibreNMS
 * @author     Pavle Obradovic <pobradovic08@gmail.com>
 */
require 'includes/html/graphs/common.inc.php';

$rrd_options .= ' -u 100 -l 0 -E -b 1024 ';

$iter = '1';

if ($width > '500') {
    $descr_len = 13;
} else {
    $descr_len = 8;
    $descr_len += round(($width - 250) / 8);
}

if ($width > '500') {
    $rrd_options .= " COMMENT:'" . substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5)) . "Total       Used       Free       (Min       Max      Ave)'";
    $rrd_options .= " COMMENT:'\l'";
} else {
    $rrd_options .= " COMMENT:'" . substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5)) . "Total       Used       Free\l'";
}

$descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr(short_hrDeviceDescr($components['name']), $descr_len);

$perc = $components['memory_used'] * 100 / $components['memory_total'];

$background = \LibreNMS\Util\Colors::percentage($perc, 75);

$rrd_options .= " DEF:qfp_used=$rrd_filename:InUse:AVERAGE";
$rrd_options .= " DEF:qfp_free=$rrd_filename:Free:AVERAGE";
$rrd_options .= " DEF:qfp_size=$rrd_filename:Total:AVERAGE";
$rrd_options .= " DEF:qfp_low_watermark=$rrd_filename:LowFreeWatermark:AVERAGE";
$rrd_options .= " DEF:qfp_ok_th=$rrd_filename:FallingThreshold:AVERAGE";
$rrd_options .= " DEF:qfp_warn_th=$rrd_filename:RisingThreshold:AVERAGE";
$rrd_options .= ' CDEF:qfp_perc=qfp_used,qfp_size,/,100,*';
$rrd_options .= ' CDEF:qfp_percx=100,qfp_perc,-';
$rrd_options .= ' AREA:qfp_perc#' . $background['right'] . ':';
$rrd_options .= ' CDEF:qfp_tmp_wmark=qfp_low_watermark,qfp_size,/,100,*';
$rrd_options .= ' CDEF:qfp_perc_wmark=100,qfp_tmp_wmark,-';

if ($width > '500') {
    $rrd_options .= ' LINE1.25:qfp_perc#' . $background['left'] . ":'$descr'";
    $rrd_options .= ' GPRINT:qfp_size:LAST:%6.2lf%sB';
    $rrd_options .= ' GPRINT:qfp_used:LAST:%6.2lf%sB';
    $rrd_options .= ' GPRINT:qfp_free:LAST:%6.2lf%sB';
    $rrd_options .= ' GPRINT:qfp_free:MIN:%5.2lf%sB';
    $rrd_options .= ' GPRINT:qfp_free:MAX:%5.2lf%sB';
    $rrd_options .= ' GPRINT:qfp_free:AVERAGE:%5.2lf%sB\\n';
    $rrd_options .= " COMMENT:'" . substr(str_pad('', ($descr_len + 12)), 0, ($descr_len + 12)) . " '";
    $rrd_options .= " GPRINT:qfp_perc:LAST:'%6.2lf%%  '";
    $rrd_options .= " GPRINT:qfp_percx:LAST:'%6.2lf%% '";
    $rrd_options .= " GPRINT:qfp_perc:MIN:'%5.2lf%% '";
    $rrd_options .= " GPRINT:qfp_perc:MAX:'%5.2lf%% '";
    $rrd_options .= ' GPRINT:qfp_perc:AVERAGE:%5.2lf%%\\n';
    $rrd_options .= " LINE2:qfp_perc_wmark#ffaaaa:'Most used'";
    $rrd_options .= " COMMENT:'\l'";
    $rrd_options .= " LINE1:qfp_warn_th#aa0000:'Threshold':dashes";
} else {
    $rrd_options .= ' LINE1.25:qfp_perc#' . $background['left'] . ":'$descr'";
    $rrd_options .= ' GPRINT:qfp_size:LAST:%6.2lf%sB';
    $rrd_options .= ' GPRINT:qfp_used:LAST:%6.2lf%sB';
    $rrd_options .= ' GPRINT:qfp_free:LAST:%6.2lf%sB';
    $rrd_options .= " COMMENT:'\l'";
    $rrd_options .= " COMMENT:'" . substr(str_pad('', ($descr_len + 12)), 0, ($descr_len + 12)) . " '";
    $rrd_options .= " GPRINT:qfp_perc:LAST:'%5.2lf%%  '";
    $rrd_options .= " GPRINT:qfp_percx:LAST:'%5.2lf%% '";
    $rrd_options .= " COMMENT:'\l'";
    $rrd_options .= " LINE2:qfp_perc_wmark#ffaaaa:'Most used'";
    $rrd_options .= " COMMENT:'\l'";
    $rrd_options .= " LINE1:qfp_warn_th#aa0000:'Threshold':dashes";
}
