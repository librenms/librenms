<?php

/**
 * Multi-sensor graph
 *
 * Graphs multiple sensors (of the same class) from different devices
 * on a single graph for comparison.
 *
 * URL format:
 * graph.php?type=multisensor_graph&id=123,456,789
 * graph.php?type=multisensor_graph&id=123,456,789&stack=1  (stacked with aggregate)
 *
 * All sensors must be of the same class (e.g., all power, all temperature).
 */

use LibreNMS\Exceptions\RrdGraphException;

require 'includes/html/graphs/common.inc.php';

// Get sensors from auth.inc.php (variable is set in same scope)
$sensors = $multisensor_sensors ?? [];

if (empty($sensors)) {
    throw new RrdGraphException('No sensors specified');
}

// Check for stacked mode
$stacked_mode = ! empty($vars['stack']);
$stacked = generate_stacked_graphs($stacked_mode);

// For stacked graphs, force Y-axis to start at 0 so we see the full stack
if ($stacked_mode) {
    $graph_params->scale_min = 0;
    $graph_params->scale_rigid = true;
}

// Validate all sensors are the same class
$sensor_class = $sensors[0]->sensor_class;
foreach ($sensors as $sensor) {
    if ($sensor->sensor_class !== $sensor_class) {
        throw new RrdGraphException('All sensors must be of the same type. Mixed types: ' . $sensor_class . ' and ' . $sensor->sensor_class);
    }
}

// Get unit information from first sensor
$unit_short = str_replace('%', '%%', $sensors[0]->unit());
$unit_long = str_replace('%', '%%', $sensors[0]->unitLong());

$graph_params->vertical_label = $sensors[0]->classDescr();

// Calculate description length based on graph width (like generic_multi_bits_separated)
if ($width > '1500') {
    $descr_len = 48;
} elseif ($width >= '500') {
    $descr_len = 20;
    $descr_len += min(28, round(($width - 500) / 25));
} else {
    $descr_len = 16;
    $descr_len += min(10, round(($width - 260) / 20));
}

// Build column header with proper alignment
$col_w = 9 + strlen($unit_short);
$header_descr = str_pad((string) $sensors[0]->classDescr(), $descr_len);
$rrd_options[] = sprintf('COMMENT:%s', substr($header_descr, 0, $descr_len));
$rrd_options[] = sprintf('COMMENT:%' . $col_w . 's', 'Current');
$rrd_options[] = sprintf('COMMENT:%' . $col_w . 's', 'Minimum');
$rrd_options[] = sprintf('COMMENT:%' . $col_w . 's', 'Maximum');
$rrd_options[] = sprintf('COMMENT:%' . $col_w . 's', 'Average');
$rrd_options[] = 'COMMENT:\l';

// Color palette - more colors for multi-sensor graphs
$colours = [
    'CC0000', // Red
    '008C00', // Green
    '4096EE', // Blue
    'FF6600', // Orange
    '73880A', // Olive
    'D01F3C', // Crimson
    '9400D3', // Dark Violet
    '00CED1', // Dark Turquoise
    'FF1493', // Deep Pink
    '8B4513', // Saddle Brown
    '2E8B57', // Sea Green
    '6A5ACD', // Slate Blue
    'FF4500', // Orange Red
    '20B2AA', // Light Sea Green
    'B22222', // Fire Brick
];

$i = 0;
$aggr_fields = '';  // For building aggregate CDEF

foreach ($sensors as $sensor) {
    $device = device_by_id_cache($sensor->device_id);
    $rrd_filename = get_sensor_rrd($device, $sensor);

    if (! Rrd::checkRrdExists($rrd_filename)) {
        continue;
    }

    // Use display name if set, otherwise fall back to short hostname
    if (! empty($device['display'])) {
        $hostname = $device['display'];
    } else {
        $hostname = $device['shortname'] ?? $device['hostname'];
        if (str_contains((string) $hostname, '.')) {
            $hostname = explode('.', (string) $hostname)[0];
        }
    }

    // Build description with device:sensor format
    $sensor_descr = $sensor->sensor_descr;

    // Calculate space for each part
    $host_len = min(12, strlen((string) $hostname));
    $sensor_len = $descr_len - $host_len - 1; // -1 for separator

    // Build the description: "hostname:sensor_descr"
    $short_host = substr((string) $hostname, 0, $host_len);
    $short_sensor = strlen((string) $sensor_descr) > $sensor_len
        ? substr((string) $sensor_descr, 0, $sensor_len - 2) . '..'
        : $sensor_descr;

    $full_descr = $short_host . ':' . $short_sensor;

    $sensor_descr_fixed = LibreNMS\Data\Store\Rrd::fixedSafeDescr($full_descr, $descr_len);
    $colour = $colours[$i % count($colours)];
    $field = 'sensor' . $sensor->sensor_id;

    $rrd_options[] = "DEF:$field=$rrd_filename:sensor:AVERAGE";

    // Handle Fahrenheit conversion if needed
    if ($unit_short == '°F') {
        $rrd_options[] = "CDEF:far{$sensor->sensor_id}=9,5,/,$field,*,32,+";
        $field = 'far' . $sensor->sensor_id;
    }

    // Build aggregate expression
    if ($i >= 1) {
        $aggr_fields .= ',';
    }
    if ($i > 1) {
        $aggr_fields .= 'ADDNAN,';
    }
    $aggr_fields .= $field;

    // Use AREA with STACK for stacked mode, LINE for normal
    if ($stacked_mode) {
        // First area has no STACK, subsequent areas stack on top
        $stack_opt = ($i > 0) ? ':STACK' : '';
        $rrd_options[] = "AREA:$field#$colour" . $stacked['transparency'] . ":$sensor_descr_fixed$stack_opt";
    } else {
        $rrd_options[] = "LINE1.5:$field#$colour:$sensor_descr_fixed";
    }

    $rrd_options[] = "GPRINT:$field:LAST:%8.2lf$unit_short";
    $rrd_options[] = "GPRINT:$field:MIN:%8.2lf$unit_short";
    $rrd_options[] = "GPRINT:$field:MAX:%8.2lf$unit_short";
    $rrd_options[] = "GPRINT:$field:AVERAGE:%8.2lf$unit_short\\l";

    $i++;
}

// Add aggregate total line for stacked mode
if ($stacked_mode && $i > 1) {
    $rrd_options[] = 'COMMENT:\l';
    $rrd_options[] = "CDEF:aggregate=$aggr_fields,ADDNAN";

    $aggr_descr = LibreNMS\Data\Store\Rrd::fixedSafeDescr('Total (Aggregate)', $descr_len);
    $rrd_options[] = "LINE1:aggregate#000000:$aggr_descr";
    $rrd_options[] = "GPRINT:aggregate:LAST:%8.2lf$unit_short";
    $rrd_options[] = "GPRINT:aggregate:MIN:%8.2lf$unit_short";
    $rrd_options[] = "GPRINT:aggregate:MAX:%8.2lf$unit_short";
    $rrd_options[] = "GPRINT:aggregate:AVERAGE:%8.2lf$unit_short\\l";
}

if ($i === 0) {
    throw new RrdGraphException('No RRD files found for specified sensors');
}
