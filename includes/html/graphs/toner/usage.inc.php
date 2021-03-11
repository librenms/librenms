<?php

$scale_min = '0';
$scale_max = '100';

require 'includes/html/graphs/common.inc.php';

$rrd_options .= " COMMENT:'                                 Cur    Max\\n'";

$colour = toner2colour($toner['printer_descr'], 100 - $toner['printer_current']);
if ($colour['left'] == null) {
    $colour['left'] = 'CC0000';
}

$descr = safedescr(substr(str_pad($toner['printer_descr'], 26), 0, 26));

$background = get_percentage_colours((100 - $toner['printer_current']));

$rrd_options .= ' DEF:toner' . $toner['id'] . '=' . $rrd_filename . ':toner:AVERAGE ';

$rrd_options .= ' LINE1:toner' . $toner['id'] . '#' . $colour['left'] . ":'" . $descr . "' ";

$rrd_options .= ' AREA:toner' . $toner['id'] . '#' . $background['right'] . ':';
$rrd_options .= ' GPRINT:toner' . $toner['id'] . ":LAST:'%5.0lf%%'";
$rrd_options .= ' GPRINT:toner' . $toner['id'] . ':MAX:%5.0lf%%\l';
