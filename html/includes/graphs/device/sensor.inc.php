<?php

require 'includes/graphs/common.inc.php';


$iter         = '1';
$rrd_options .= " COMMENT:'$unit_long                  Cur       Min       Max\\n'";

foreach (dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? ORDER BY `sensor_index`', array($class, $device['device_id'])) as $sensor) {
    // FIXME generic colour function
    switch ($iter) {
        case '1':
            $colour = 'CC0000';
            break;

        case '2':
            $colour = '008C00';
            break;

        case '3':
            $colour = '4096EE';
            break;

        case '4':
            $colour = '73880A';
            break;

        case '5':
            $colour = 'D01F3C';
            break;

        case '6':
            $colour = '36393D';
            break;

        case '7':
        default:
            $colour = 'FF0084';
            unset($iter);
            break;
    }//end switch

    $sensor['sensor_descr_fixed'] = rrdtool_escape($sensor['sensor_descr'], 12);
    $rrd_file     = get_sensor_rrd($device, $sensor);
    $rrd_options .= ' DEF:sensor'.$sensor['sensor_id']."=$rrd_file:sensor:AVERAGE ";
    $rrd_options .= ' LINE1:sensor'.$sensor['sensor_id'].'#'.$colour.":'".str_replace(':', '\:', str_replace('\*', '*', $sensor['sensor_descr_fixed']))."'";
    $rrd_options .= ' GPRINT:sensor'.$sensor['sensor_id'].':LAST:%4.1lf'.$unit.' ';
    $rrd_options .= ' GPRINT:sensor'.$sensor['sensor_id'].':MIN:%4.1lf'.$unit.' ';
    $rrd_options .= ' GPRINT:sensor'.$sensor['sensor_id'].':MAX:%4.1lf'.$unit.'\\\l ';
    $iter++;
}//end foreach
