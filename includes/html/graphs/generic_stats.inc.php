<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage graphs
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

require 'includes/html/graphs/common.inc.php';

$stacked = generate_stacked_graphs();

if (! isset($munge)) {
    $munge = false;
}

if (! isset($no_hourly)) {
    $no_hourly = false;
}

if (! isset($no_daily)) {
    $no_daily = false;
}

if (! isset($no_weekly)) {
    $no_weekly = false;
}

if (! isset($no_percentile)) {
    $no_percentile = false;
}

if (! isset($colours)) {
    $colours = 'rainbow_stats_purple';
}

if (! isset($descr_len)) {
    $descr_len = 12;
}

if ($nototal) {
    $descr_len += '2';
    $unitlen += '2';
}

if ($height > 99) {
    $rrd_options .= " COMMENT:'" . \LibreNMS\Data\Store\Rrd::fixedSafeDescr($unit_text, $descr_len) . "      Now      Min      Max     Avg\l'";
}

$i = 0;
$iter = 0;

if (! isset($colour)) {
    $colour = \LibreNMS\Config::get("graph_colours.$colours.$iter");
    $iter++;
}

if (! isset($colourA)) {
    $colourA = $colour;
}

if (! isset($colourAalpha)) {
    $colourAalpha = 33;
}

if (! isset($colour25th)) {
    if (! \LibreNMS\Config::get("graph_colours.$colours.$iter")) {
        $iter = 0;
    }
    $colour25th = \LibreNMS\Config::get("graph_colours.$colours.$iter");
    $iter++;
}

if (! isset($colour50th)) {
    if (! \LibreNMS\Config::get("graph_colours.$colours.$iter")) {
        $iter = 0;
    }
    $colour50th = \LibreNMS\Config::get("graph_colours.$colours.$iter");
    $iter++;
}

if (! isset($colour75th)) {
    if (! \LibreNMS\Config::get("graph_colours.$colours.$iter")) {
        $iter = 0;
    }
    $colour75th = \LibreNMS\Config::get("graph_colours.$colours.$iter");
    $iter++;
}

if (! isset($colour1h)) {
    if (! \LibreNMS\Config::get("graph_colours.$colours.$iter")) {
        $iter = 0;
    }
    $colour1h = \LibreNMS\Config::get("graph_colours.$colours.$iter");
    $iter++;
}

if (! isset($colour1d)) {
    if (! \LibreNMS\Config::get("graph_colours.$colours.$iter")) {
        $iter = 0;
    }
    $colour1d = \LibreNMS\Config::get("graph_colours.$colours.$iter");
    $iter++;
}

if (! isset($colour1w)) {
    if (! \LibreNMS\Config::get("graph_colours.$colours.$iter")) {
        $iter = 0;
    }
    $colour1w = \LibreNMS\Config::get("graph_colours.$colours.$iter");
    $iter++;
}

$graph_stat_percentile_disable = \LibreNMS\Config::get('graph_stat_percentile_disable');

$descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($descr, $descr_len);

if ($height > 25) {
    if (! $no_hourly) {
        $descr_1h = \LibreNMS\Data\Store\Rrd::fixedSafeDescr('1 hour avg', $descr_len);
    }
    if (! $no_daily) {
        $descr_1d = \LibreNMS\Data\Store\Rrd::fixedSafeDescr('1 day avg', $descr_len);
    }
    if (! $no_weekly) {
        $descr_1w = \LibreNMS\Data\Store\Rrd::fixedSafeDescr('1 week avg', $descr_len);
    }
}

$id = 'ds0';
if ($munge) {
    $id = 'dsm0';
}

$rrd_options .= ' DEF:ds0' . "=$filename:$ds:AVERAGE";

$munge_helper = '';
if ($munge) {
    if (! isset($munge_opts)) {
        $munge_opts = '86400,/';
    }
    $rrd_options .= ' CDEF:dsm0=ds0,' . $munge_opts;
    $munge_helper = 'ds';
}

$rrd_optionsb .= ' AREA:' . $id . '#' . $colourA . $colourAalpha;
$rrd_optionsb .= ' LINE1.25:' . $id . '#' . $colour . ":'$descr'";

if ($height > 25) {
    if (! $no_hourly) {
        $rrd_options .= ' DEF:' . $id . "1h$munge_helper=$filename:$ds:AVERAGE:step=3600";
    }
    if ($munge) {
        $rrd_options .= ' CDEF:dsm01h=dsm01hds,' . $munge_opts;
    }
    $rrd_options .= ' VDEF:' . $id . '50th=' . $id . ',50,PERCENTNAN';
    $rrd_options .= ' VDEF:' . $id . '25th=' . $id . ',25,PERCENTNAN';
    $rrd_options .= ' VDEF:' . $id . '75th=' . $id . ',75,PERCENTNAN';

    // the if is needed as with out it the group page will case an error
    // devices/group=1/format=graph_poller_perf/from=-24hour/to=now/
    if (is_numeric($vars['to']) && is_numeric($vars['from'])) {
        $time_diff = $vars['to'] - $vars['from'];
    } else {
        $time_diff = 1;
    }
    // displays nan if less than 17 hours
    if (! $no_daily) {
        if ($time_diff >= 61200) {
            $rrd_options .= ' DEF:' . $id . "1d$munge_helper=$filename:$ds:AVERAGE:step=86400";
            if ($munge) {
                $rrd_options .= ' CDEF:dsm01d=dsm01dds,' . $munge_opts;
            }
        }
    }

    // weekly breaks and causes issues if it is less than 8 days
    if (! $no_weekly) {
        if ($time_diff >= 691200) {
            $rrd_options .= ' DEF:' . $id . "1w$munge_helper=$filename:$ds:AVERAGE:step=604800";
            if ($munge) {
                $rrd_options .= ' CDEF:dsm01w=dsm01wds,' . $munge_opts;
            }
        }
    }

    $rrd_optionsb .= ' GPRINT:' . $id . ':LAST:%5.' . $float_precision . 'lf%s' . $units . ' GPRINT:' . $id . ':MIN:%5.' . $float_precision . 'lf%s' . $units;
    $rrd_optionsb .= ' GPRINT:' . $id . ':MAX:%5.' . $float_precision . 'lf%s' . $units . ' GPRINT:' . $id . ":AVERAGE:'%5." . $float_precision . "lf%s$units\\n'";

    if (! $no_hourly) {
        $rrd_optionsb .= ' LINE1.25:' . $id . '1h#' . $colour1h . ":'$descr_1h'";
        $rrd_optionsb .= ' GPRINT:' . $id . '1h:LAST:%5.' . $float_precision . 'lf%s' . $units . ' GPRINT:' . $id . '1h:MIN:%5.' . $float_precision . 'lf%s' . $units;
        $rrd_optionsb .= ' GPRINT:' . $id . '1h:MAX:%5.' . $float_precision . 'lf%s' . $units . ' GPRINT:' . $id . "1h:AVERAGE:'%5." . $float_precision . "lf%s$units\\n'";
    }

    if (! $no_daily) {
        if ($time_diff >= 61200) {
            $rrd_optionsb .= ' LINE1.25:' . $id . '1d#' . $colour1d . ":'$descr_1d'";
            $rrd_optionsb .= ' GPRINT:' . $id . '1d:LAST:%5.' . $float_precision . 'lf%s' . $units . ' GPRINT:' . $id . '1d:MIN:%5.' . $float_precision . 'lf%s' . $units;
            $rrd_optionsb .= ' GPRINT:' . $id . '1d:MAX:%5.' . $float_precision . 'lf%s' . $units . ' GPRINT:' . $id . "1d:AVERAGE:'%5." . $float_precision . "lf%s$units\\n'";
        }
    }

    if (! $no_weekly) {
        if ($time_diff >= 691200) {
            $rrd_optionsb .= ' LINE1.25:' . $id . '1w#' . $colour1w . ":'$descr_1w'";
            $rrd_optionsb .= ' GPRINT:' . $id . '1w:LAST:%5.' . $float_precision . 'lf%s' . $units . ' GPRINT:' . $id . '1w:MIN:%5.' . $float_precision . 'lf%s' . $units;
            $rrd_optionsb .= ' GPRINT:' . $id . '1w:MAX:%5.' . $float_precision . 'lf%s' . $units . ' GPRINT:' . $id . "1w:AVERAGE:'%5." . $float_precision . "lf%s$units\\n'";
        }
    }

    if (! $no_percentile) {
        if (! $graph_stat_percentile_disable) {
            $rrd_optionsb .= ' HRULE:' . $id . '25th#' . $colour25th . ':25th_Percentile';
            $rrd_optionsb .= ' GPRINT:' . $id . '25th:%' . $float_precision . 'lf%s\n';

            $rrd_optionsb .= ' HRULE:' . $id . '50th#' . $colour50th . ':50th_Percentile';
            $rrd_optionsb .= ' GPRINT:' . $id . '50th:%' . $float_precision . 'lf%s\n';

            $rrd_optionsb .= ' HRULE:' . $id . '75th#' . $colour75th . ':75th_Percentile';
            $rrd_optionsb .= ' GPRINT:' . $id . '75th:%' . $float_precision . 'lf%s\n';
        }
    }
}
$rrd_options .= $rrd_optionsb;
$rrd_options .= ' HRULE:0#555555';

unset($stacked);
