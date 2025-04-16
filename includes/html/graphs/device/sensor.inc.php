<?php

use App\Models\Sensor;
use LibreNMS\Exceptions\RrdGraphException;

require 'includes/html/graphs/common.inc.php';

$sensors = Sensor::where('sensor_class', $class)->where('device_id', $device['device_id'])->orderBy('sensor_descr')->get();

if ($sensors->isEmpty()) {
    throw new RrdGraphException('No Sensors');
}

$unit_short = str_replace('%', '%%', $sensors->first()->unit());
$unit_long = str_replace('%', '%%', $sensors->first()->unitLong());

$col_w = 7 + strlen($unit_short);
$rrd_options .= " COMMENT:'" . str_pad($unit_long, 19) . str_pad('Cur', $col_w) . str_pad('Min', $col_w) . str_pad('Max', $col_w) . "Avg\\n'";

foreach ($sensors as $index => $sensor) {
    // FIXME generic colour function
    $colour = match ($index % 7) {
        0 => 'CC0000',
        1 => '008C00',
        2 => '4096EE',
        3 => '73880A',
        4 => 'D01F3C',
        5 => '36393D',
        default => 'FF0084',
    };

    $sensor_descr_fixed = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($sensor->sensor_descr, 12);
    $rrd_filename = get_sensor_rrd($device, $sensor);
    $field = 'sensor' . $sensor->sensor_id;
    $rrd_options .= " DEF:$field=$rrd_filename:sensor:AVERAGE";

    if ($unit_short == '°F') {
        $rrd_options .= " CDEF:far{$sensor->sensor_id}=9,5,/,$field,*,32,+ ";
        $field = 'far' . $sensor->sensor_id;
    }

    $rrd_options .= " LINE1:$field#$colour:'$sensor_descr_fixed'";
    $rrd_options .= " GPRINT:$field:LAST:%5.1lf$unit_short";
    $rrd_options .= " GPRINT:$field:MIN:%5.1lf$unit_short";
    $rrd_options .= " GPRINT:$field:MAX:%5.1lf$unit_short";
    $rrd_options .= " GPRINT:$field:AVERAGE:%5.2lf$unit_short\\l ";
    $iter++;
}//end foreach
