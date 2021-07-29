<?php

require 'includes/html/graphs/common.inc.php';

$col_w = 7 + strlen($unit);
$rrd_options .= " COMMENT:'" . str_pad($unit_long, 19) . str_pad('Cur', $col_w) . str_pad('Min', $col_w) . "Max\\n'";

foreach (dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? ORDER BY `sensor_index`', [$class, $device['device_id']]) as $index => $sensor) {
    // FIXME generic colour function
    switch ($index % 7) {
        case 0:
            $colour = 'CC0000';
            break;

        case 1:
            $colour = '008C00';
            break;

        case 2:
            $colour = '4096EE';
            break;

        case 3:
            $colour = '73880A';
            break;

        case 4:
            $colour = 'D01F3C';
            break;

        case 5:
            $colour = '36393D';
            break;

        case 6:
        default:
            $colour = 'FF0084';
    }//end switch

    $sensor_descr_fixed = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($sensor['sensor_descr'], 12);
    $rrd_filename = get_sensor_rrd($device, $sensor);
    $rrd_options .= " DEF:sensor{$sensor['sensor_id']}=$rrd_filename:sensor:AVERAGE";
    $rrd_options .= " LINE1:sensor{$sensor['sensor_id']}#$colour:'$sensor_descr_fixed'";
    $rrd_options .= " GPRINT:sensor{$sensor['sensor_id']}:LAST:%5.1lf$unit";
    $rrd_options .= " GPRINT:sensor{$sensor['sensor_id']}:MIN:%5.1lf$unit";
    $rrd_options .= " GPRINT:sensor{$sensor['sensor_id']}:MAX:%5.1lf$unit\\l ";
    $iter++;
}//end foreach
