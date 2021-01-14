<?php

use LibreNMS\Config;
use LibreNMS\Util\Clean;

if ($_GET['from']) {
    $from = parse_at_time($_GET['from']);
}

if ($_GET['to']) {
    $to = parse_at_time($_GET['to']);
}

if ($_GET['width']) {
    $width = (int) $_GET['width'];
}

if ($_GET['height']) {
    $height = (int) $_GET['height'];
}

if ($_GET['inverse']) {
    $in = 'out';
    $out = 'in';
    $inverse = true;
} else {
    $in = 'in';
    $out = 'out';
}

if ($_GET['legend'] == 'no') {
    $rrd_options .= ' -g';
}

if (isset($_GET['nototal'])) {
    $nototal = ((bool) $_GET['nototal']);
} else {
    $nototal = true;
}

if (isset($_GET['nodetails'])) {
    $nodetails = ((bool) $_GET['nodetails']);
} else {
    $nodetails = false;
}

if (isset($_GET['noagg'])) {
    $noagg = ((bool) $_GET['noagg']);
} else {
    $noagg = true;
}

if ($_GET['title'] == 'yes') {
    $rrd_options .= " --title='" . $graph_title . "' ";
}

if (isset($_GET['graph_title'])) {
    $rrd_options .= " --title='" . Clean::alphaDashSpace($_GET['graph_title']) . "' ";
}

if (! isset($scale_min) && ! isset($scale_max)) {
    $rrd_options .= ' --alt-autoscale-max';
}

if (! isset($scale_min) && ! isset($scale_max) && ! isset($norigid)) {
    $rrd_options .= ' --rigid';
}

if (isset($scale_min)) {
    $rrd_options .= " -l $scale_min";
}

if (isset($scale_max)) {
    $rrd_options .= " -u $scale_max";
}

if (isset($scale_rigid)) {
    $rrd_options .= ' -r';
}

if (! isset($float_precision)) {
    $float_precision = 2;
}

$rrd_options .= ' -E --start ' . $from . ' --end ' . $to . ' --width ' . $width . ' --height ' . $height . ' ';

if (Config::get('applied_site_style') == 'dark') {
    $rrd_options .= \LibreNMS\Config::get('rrdgraph_def_text_dark') . ' -c FONT#' . ltrim(\LibreNMS\Config::get('rrdgraph_def_text_color_dark'), '#');
} else {
    $rrd_options .= \LibreNMS\Config::get('rrdgraph_def_text') . ' -c FONT#' . ltrim(\LibreNMS\Config::get('rrdgraph_def_text_color'), '#');
}

if ($_GET['bg']) {
    $rrd_options .= ' -c CANVAS#' . Clean::alphaDash($_GET['bg']) . ' ';
}

if ($_GET['font']) {
    $rrd_options .= ' -c FONT#' . Clean::alphaDash($_GET['font']) . ' ';
}

// $rrd_options .= " -c BACK#FFFFFF";
if ($height < '99') {
    $rrd_options .= ' --only-graph';
}

if ($width <= '300') {
    $rrd_options .= ' --font LEGEND:7:' . \LibreNMS\Config::get('mono_font') . ' --font AXIS:6:' . \LibreNMS\Config::get('mono_font');
} else {
    $rrd_options .= ' --font LEGEND:8:' . \LibreNMS\Config::get('mono_font') . ' --font AXIS:7:' . \LibreNMS\Config::get('mono_font');
}

$rrd_options .= ' --font-render-mode normal';

if (isset($_GET['absolute']) && $_GET['absolute'] == '1') {
    $rrd_options .= ' --full-size-mode';
}
