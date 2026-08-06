<?php

use App\Facades\Rrd;
use App\Models\Sensor;
use LibreNMS\Exceptions\RrdGraphException;

require 'includes/html/graphs/common.inc.php';

$graph_params->scale_min = 0;
// scale_min disables GraphParameters' automatic --alt-autoscale-max
// (only added when scale_min and scale_max are both null), so the upper
// bound would otherwise be picked by the coarser default algorithm and
// leave a lot of empty space at the top.
$rrd_options[] = '--alt-autoscale-max';

$sensors = Sensor::where('sensor_class', 'current')->where('device_id', $device['device_id'])->get();

if ($sensors->isEmpty()) {
    throw new RrdGraphException('No Sensors');
}

$unit_short = 'A';

// sum as an RPN expression: N values on the stack -> (N-1) "+" ops
$abb_sum_cdef = fn (array $fields): string => rtrim(implode(',', $fields) . ',' . str_repeat('+,', count($fields) - 1), ',');

// Sum stays UNKNOWN only when every branch feeding it is UNKNOWN at that
// point (e.g. the newest, not-yet-polled RRA bucket); otherwise missing
// branches are treated as 0 so one gap doesn't blank out the whole sum.
$abb_safe_sum_cdef = function (string $out, array $z_fields, array $raw_fields) use (&$rrd_options, $abb_sum_cdef): void {
    $rrd_options[] = "CDEF:{$out}_raw=" . $abb_sum_cdef($z_fields);
    $abb_unknown_terms = array_map(static fn (string $f): string => "{$f},UN", $raw_fields);
    $rrd_options[] = "CDEF:{$out}_unk=" . $abb_sum_cdef($abb_unknown_terms);
    $rrd_options[] = "CDEF:{$out}={$out}_unk," . count($raw_fields) . ",GE,UNKN,{$out}_raw,IF";
};

$abb_total_fields = [];
$abb_total_raw_fields = [];
$abb_phase_fields = []; // '1'/'2'/'3' => [field names]
$abb_phase_raw_fields = [];

foreach ($sensors as $sensor) {
    $abb_field = 'sensor' . $sensor->sensor_id;
    $rrd_filename = Rrd::name($device['hostname'], get_sensor_rrd_name($device, $sensor));
    $rrd_options[] = "DEF:{$abb_field}=$rrd_filename:sensor:AVERAGE";

    $abb_field_z = $abb_field . 'z';
    $rrd_options[] = "CDEF:{$abb_field_z}={$abb_field},UN,0,{$abb_field},IF";
    $abb_total_fields[] = $abb_field_z;
    $abb_total_raw_fields[] = $abb_field;

    if (preg_match('/\(L(\d)\)\s*$/', (string) $sensor->sensor_descr, $abb_m)) {
        $abb_phase_fields[$abb_m[1]][] = $abb_field_z;
        $abb_phase_raw_fields[$abb_m[1]][] = $abb_field;
    }
}

$abb_safe_sum_cdef('abbtotal', $abb_total_fields, $abb_total_raw_fields);
$rrd_options[] = 'AREA:abbtotal#CCCCCC:' . str_pad('Total', 12);
$rrd_options[] = "GPRINT:abbtotal:LAST:%5.1lf $unit_short\\l";

ksort($abb_phase_fields);
$abb_phase_colours = ['1' => 'CC0000', '2' => '008C00', '3' => '4096EE'];
foreach ($abb_phase_fields as $abb_phase => $abb_fields) {
    $abb_pfield = 'abbphase' . $abb_phase;
    $abb_safe_sum_cdef($abb_pfield, $abb_fields, $abb_phase_raw_fields[$abb_phase]);
    $abb_colour = $abb_phase_colours[$abb_phase] ?? '36393D';
    $rrd_options[] = "LINE2:{$abb_pfield}#{$abb_colour}:" . str_pad("Phase L{$abb_phase}", 12);
    $rrd_options[] = "GPRINT:{$abb_pfield}:LAST:%5.1lf $unit_short\\l";
}

unset($abb_sum_cdef, $abb_safe_sum_cdef, $abb_total_fields, $abb_total_raw_fields, $abb_phase_fields, $abb_phase_raw_fields, $abb_field, $abb_field_z, $abb_m, $abb_phase, $abb_fields, $abb_pfield, $abb_colour, $abb_phase_colours);
