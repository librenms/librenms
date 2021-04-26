<?php

$scale_min = '0';
$scale_max = '100';

require 'includes/html/graphs/common.inc.php';

$rrd_options .= " COMMENT:'                                 Cur    Max\\n'";

$colour = toner2colour($toner['supply_descr'], 100 - $toner['supply_current']);
if ($colour['left'] == null) {
    $colour['left'] = 'CC0000';
}

$descr = \LibreNMS\Data\Store\Rrd::safeDescr(substr(str_pad($toner['supply_descr'], 26), 0, 26));

$background = \LibreNMS\Util\Colors::percentage((100 - $toner['supply_current']));

$rrd_options .= ' DEF:toner' . $toner['supply_id'] . '=' . $rrd_filename . ':toner:AVERAGE ';

$rrd_options .= ' LINE1:toner' . $toner['supply_id'] . '#' . $colour['left'] . ":'" . $descr . "' ";

$rrd_options .= ' AREA:toner' . $toner['supply_id'] . '#' . $background['right'] . ':';
$rrd_options .= ' GPRINT:toner' . $toner['supply_id'] . ":LAST:'%5.0lf%%'";
$rrd_options .= ' GPRINT:toner' . $toner['supply_id'] . ':MAX:%5.0lf%%\l';
