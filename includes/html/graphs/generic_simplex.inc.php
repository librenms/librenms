<?php

// Draw generic bits graph
// args: ds_in, ds_out, rrd_filename, bg, legend, from, to, width, height, inverse, percentile
require 'includes/html/graphs/common.inc.php';

$multiplier = $multiplier ?? false;
$print_total = $print_total ?? false;
$percentile = \LibreNMS\Config::get('percentile_value');
$unit_text = $unit_text ?? '';
$line_text = $line_text ?? '';
$previous = $_GET['previous'] ?? 'no';

$unit_text = str_pad(substr($unit_text, 0, 18), 18);
$line_text = str_pad(substr($line_text, 0, 12), 12);

if ($multiplier) {
    if (empty($multiplier_action)) {
        $multiplier_action = '*';
    }

    $rrd_options .= ' DEF:' . $ds . '_o=' . $rrd_filename . ':' . $ds . ':AVERAGE';
    $rrd_options .= ' CDEF:' . $ds . '=' . $ds . "_o,$multiplier,$multiplier_action";
    $rrd_options .= ' DEF:' . $ds . '_o_max=' . $rrd_filename . ':' . $ds . ':MAX';
    $rrd_options .= ' CDEF:' . $ds . '_max=' . $ds . "_o_max,$multiplier,$multiplier_action";
} else {
    $rrd_options .= ' DEF:' . $ds . '=' . $rrd_filename . ':' . $ds . ':AVERAGE';
    $rrd_options .= ' DEF:' . $ds . '_max=' . $rrd_filename . ':' . $ds . ':MAX';
}

if ($print_total) {
    $rrd_options .= ' VDEF:' . $ds . '_total=' . $ds . ',TOTAL';
}

if ($percentile) {
    $rrd_options .= ' VDEF:' . $ds . '_percentile=' . $ds . '_max,' . $percentile . ',PERCENTNAN';
}

if ($previous == 'yes') {
    if ($multiplier) {
        if (empty($multiplier_action)) {
            $multiplier_action = '*';
        }

        $rrd_options .= ' DEF:' . $ds . '_oX=' . $rrd_filename . ':' . $ds . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' SHIFT:' . $ds . "_oX:$period";
        $rrd_options .= ' CDEF:' . $ds . 'X=' . $ds . "_oX,$multiplier,*";
        $rrd_options .= ' DEF:' . $ds . '_o_maxX=' . $rrd_filename . ':' . $ds . ':MAX:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' SHIFT:' . $ds . "_o_maxX:$period";
        $rrd_options .= ' CDEF:' . $ds . '_maxX=' . $ds . "_oX,$multiplier,*";
    } else {
        $rrd_options .= ' DEF:' . $ds . 'X=' . $rrd_filename . ':' . $ds . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' SHIFT:' . $ds . "X:$period";
        $rrd_options .= ' DEF:' . $ds . '_maxX=' . $rrd_filename . ':' . $ds . ':MAX:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' SHIFT:' . $ds . "_maxX:$period";
    }

    if ($print_total) {
        $rrd_options .= ' VDEF:' . $ds . '_totalX=' . $ds . ',TOTAL';
    }

    if ($percentile) {
        $rrd_options .= ' VDEF:' . $ds . '_percentileX=' . $ds . '_maxX,' . $percentile . ',PERCENTNAN';
    }
}//end if

$rrd_options .= ' AREA:' . $ds . '#' . $colour_area . ':';

$rrd_options .= " COMMENT:'" . $unit_text . 'Now       Ave      Max';

if ($percentile) {
    $rrd_options .= '      ' . $percentile . 'th %';
}

$rrd_options .= "\\n'";
$rrd_options .= ' LINE1.25:' . $ds . '#' . $colour_line . ":'" . $line_text . "'";
$rrd_options .= ' GPRINT:' . $ds . ':LAST:%6.' . $float_precision . 'lf%s';
$rrd_options .= ' GPRINT:' . $ds . ':AVERAGE:%6.' . $float_precision . 'lf%s';
$rrd_options .= ' GPRINT:' . $ds . ':MAX:%6.' . $float_precision . 'lf%s';

if ($percentile) {
    $rrd_options .= ' GPRINT:' . $ds . '_percentile:%6.' . $float_precision . 'lf%s';
}

$rrd_options .= '\\n';
$rrd_options .= ' COMMENT:\\n';

if ($print_total) {
    $rrd_options .= ' GPRINT:' . $ds . '_total:Total" %6.' . $float_precision . 'lf%s"\\l';
}

if ($percentile && \LibreNMS\Config::get('percentile_line')) {
    $rrd_options .= ' LINE1:' . $ds . '_percentile#aa0000';
}

if ($previous == 'yes') {
    $rrd_options .= ' LINE1.25:' . $ds . "X#666666:'Prev        '";
    $rrd_options .= ' AREA:' . $ds . 'X#99999966:';
    $rrd_options .= ' GPRINT:' . $ds . 'X:LAST:%6.' . $float_precision . 'lf%s';
    $rrd_options .= ' GPRINT:' . $ds . 'X:AVERAGE:%6.' . $float_precision . 'lf%s';
    $rrd_options .= ' GPRINT:' . $ds . 'X:MAX:%6.' . $float_precision . 'lf%s';
    if ($percentile) {
        $rrd_options .= ' GPRINT:' . $ds . '_percentileX:%6.' . $float_precision . 'lf%s';
    }
    $rrd_options .= '\\n';
}
