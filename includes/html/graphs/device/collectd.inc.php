<?php

/*
 * Copyright (C) 2009  Bruno Prémont <bonbons AT linux-vserver.org>
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; only version 2 of the License is applicable.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied wadsnty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

use LibreNMS\Config;

require 'includes/html/collectd/config.php';
require 'includes/html/collectd/functions.php';
require 'includes/html/collectd/definitions.php';

function makeTextBlock($text, $fontfile, $fontsize, $width)
{
    // TODO: handle explicit line-break!
    $words = explode(' ', $text);
    $lines = [$words[0]];
    $currentLine = 0;
    foreach ($words as $word) {
        $lineSize = imagettfbbox($fontsize, 0, $fontfile, $lines[$currentLine] . ' ' . $word);
        if (($lineSize[2] - $lineSize[0]) < $width) {
            $lines[$currentLine] .= ' ' . $word;
        } else {
            $currentLine++;
            $lines[$currentLine] = $word;
        }
    }

    error_log(sprintf('Handles message "%s", %d words => %d/%d lines', $text, count($words), $currentLine, count($lines)));

    return implode("\n", $lines);
}//end makeTextBlock()

/**
 * No RRD files found that could match request
 * @code HTTP error code
 * @code_msg Short text description of HTTP error code
 * @title Title for fake RRD graph
 * @msg Complete error message to display in place of graph content
 */
function error($code, $code_msg, $title, $msg)
{
    header(sprintf('HTTP/1.0 %d %s', $code, $code_msg));
    header('Pragma: no-cache');
    header('Expires: Mon, 01 Jan 2008 00:00:00 CET');
    header('Content-Type: image/png');
    $w = (Config::get('rrd_width') + 81);
    $h = (Config::get('rrd_height') + 79);

    $png = imagecreate($w, $h);
    $c_bkgnd = imagecolorallocate($png, 240, 240, 240);
    $c_fgnd = imagecolorallocate($png, 255, 255, 255);
    $c_blt = imagecolorallocate($png, 208, 208, 208);
    $c_brb = imagecolorallocate($png, 160, 160, 160);
    $c_grln = imagecolorallocate($png, 114, 114, 114);
    $c_grarr = imagecolorallocate($png, 128, 32, 32);
    $c_txt = imagecolorallocate($png, 0, 0, 0);
    $c_etxt = imagecolorallocate($png, 64, 0, 0);

    if (function_exists('imageantialias')) {
        imageantialias($png, true);
    }

    imagefilledrectangle($png, 0, 0, $w, $h, $c_bkgnd);
    imagefilledrectangle($png, 51, 33, ($w - 31), ($h - 47), $c_fgnd);
    imageline($png, 51, 30, 51, ($h - 43), $c_grln);
    imageline($png, 48, ($h - 46), ($w - 28), ($h - 46), $c_grln);
    imagefilledpolygon($png, [49, 30, 51, 26, 53, 30], 3, $c_grarr);
    imagefilledpolygon($png, [$w - 28, $h - 48, $w - 24, $h - 46, $w - 28, $h - 44], 3, $c_grarr);
    imageline($png, 0, 0, $w, 0, $c_blt);
    imageline($png, 0, 1, $w, 1, $c_blt);
    imageline($png, 0, 0, 0, $h, $c_blt);
    imageline($png, 1, 0, 1, $h, $c_blt);
    imageline($png, ($w - 1), 0, ($w - 1), $h, $c_brb);
    imageline($png, ($w - 2), 1, ($w - 2), $h, $c_brb);
    imageline($png, 1, ($h - 2), $w, ($h - 2), $c_brb);
    imageline($png, 0, ($h - 1), $w, ($h - 1), $c_brb);

    imagestring($png, 4, ceil(($w - strlen($title) * imagefontwidth(4)) / 2), 10, $title, $c_txt);
    imagestring($png, 5, 60, 35, sprintf('%s [%d]', $code_msg, $code), $c_etxt);
    if (function_exists('imagettfbbox') && is_file(Config::get('error_font'))) {
        // Detailled error message
        $errorfont = Config::get('error_font');
        $fmt_msg = makeTextBlock($msg, $errorfont, 10, ($w - 86));
        $fmtbox = imagettfbbox(12, 0, $errorfont, $fmt_msg);
        imagettftext($png, 10, 0, 55, (35 + 3 + imagefontwidth(5) - $fmtbox[7] + $fmtbox[1]), $c_txt, $errorfont, $fmt_msg);
    } else {
        imagestring($png, 4, 53, (35 + 6 + imagefontwidth(5)), $msg, $c_txt);
    }

    imagepng($png);
    imagedestroy($png);
}//end error()

/**
 * No RRD files found that could match request
 */
function error404($title, $msg)
{
    return error(404, 'Not found', $title, $msg);
}//end error404()

function error500($title, $msg)
{
    return error(500, 'Not found', $title, $msg);
}//end error500()

/**
 * Incomplete / invalid request
 */
function error400($title, $msg)
{
    return error(400, 'Bad request', $title, $msg);
}//end error400()

// Process input arguments
// $host   = read_var('host', $_GET, null);
$host = $device['hostname'];
if (is_null($host)) {
    return error400('?/?-?/?', 'Missing host name');
} elseif (! is_string($host)) {
    return error400('?/?-?/?', 'Expecting exactly 1 host name');
} elseif (strlen($host) == 0) {
    return error400('?/?-?/?', 'Host name may not be blank');
}

$plugin = read_var('c_plugin', $_GET, null);
if (is_null($plugin)) {
    return error400($host . '/?-?/?', 'Missing plugin name');
} elseif (! is_string($plugin)) {
    return error400($host . '/?-?/?', 'Plugin name must be a string');
} elseif (strlen($plugin) == 0) {
    return error400($host . '/?-?/?', 'Plugin name may not be blank');
}

$pinst = read_var('c_plugin_instance', $_GET, '');
if (! is_string($pinst)) {
    return error400($host . '/' . $plugin . '-?/?', 'Plugin instance name must be a string');
}

$type = read_var('c_type', $_GET, '');
if (is_null($type)) {
    return error400($host . '/' . $plugin . (strlen($pinst) ? '-' . $pinst : '') . '/?', 'Missing type name');
} elseif (! is_string($type)) {
    return error400($host . '/' . $plugin . (strlen($pinst) ? '-' . $pinst : '') . '/?', 'Type name must be a string');
} elseif (strlen($type) == 0) {
    return error400($host . '/' . $plugin . (strlen($pinst) ? '-' . $pinst : '') . '/?', 'Type name may not be blank');
}

$tinst = read_var('c_type_instance', $_GET, '');

$graph_identifier = $host . '/' . $plugin . (strlen($pinst) ? '-' . $pinst : '') . '/' . $type . (strlen($tinst) ? '-' . $tinst : '-*');

$timespan = read_var('timespan', $_GET, Config::get('timespan.0.name'));
$timespan_ok = false;
foreach (Config::get('timespan') as &$ts) {
    if ($ts['name'] == $timespan) {
        $timespan_ok = true;
    }
}

if (! $timespan_ok) {
    return error400($graph_identifier, 'Unknown timespan requested');
}

$logscale = (bool) read_var('logarithmic', $_GET, false);
$tinylegend = (bool) read_var('tinylegend', $_GET, false);

// Check that at least 1 RRD exists for the specified request
$all_tinst = collectd_list_tinsts($host, $plugin, $pinst, $type);
if (count($all_tinst) == 0) {
    return error404($graph_identifier, 'No rrd file found for graphing');
}

// Now that we are read, do the bulk work
load_graph_definitions($logscale, $tinylegend);

$pinst = strlen($pinst) == 0 ? null : $pinst;
$tinst = strlen($tinst) == 0 ? null : $tinst;

$opts = [];
$opts['timespan'] = $timespan;
if ($logscale) {
    $opts['logarithmic'] = 1;
}

if ($tinylegend) {
    $opts['tinylegend'] = 1;
}

$rrd_cmd = false;
if (isset($MetaGraphDefs[$type])) {
    $identifiers = [];
    foreach ($all_tinst as &$atinst) {
        $identifiers[] = collectd_identifier($host, $plugin, $type, is_null($pinst) ? '' : $pinst, $atinst);
    }

    collectd_flush($identifiers);
    $rrd_cmd = $MetaGraphDefs[$type]($host, $plugin, $pinst, $type, $all_tinst, $opts);
} else {
    if (! in_array(is_null($tinst) ? '' : $tinst, $all_tinst)) {
        return error404($host . '/' . $plugin . (! is_null($pinst) ? '-' . $pinst : '') . '/' . $type . (! is_null($tinst) ? '-' . $tinst : ''), 'No rrd file found for graphing');
    }

    collectd_flush(collectd_identifier($host, $plugin, $type, is_null($pinst) ? '' : $pinst, is_null($tinst) ? '' : $tinst));
    if (isset($GraphDefs[$type])) {
        $rrd_cmd = collectd_draw_generic($timespan, $host, $plugin, $type, $pinst, $tinst);
    } else {
        $rrd_cmd = collectd_draw_rrd($host, $plugin, $type, $pinst, $tinst);
    }
}

if (isset($rrd_cmd)) {
    $from = (int) ($_GET['from'] ?? time() - 86400);
    $to = (int) ($_GET['to'] ?? time());

    $rrd_cmd .= ' -s ' . $from . ' -e ' . $to;
}

if ($_GET['legend'] == 'no') {
    $rrd_cmd .= ' -g ';
}

if ($height < '99') {
    $rrd_cmd .= ' --only-graph ';
}

if ($width <= '300') {
    $rrd_cmd .= ' --font LEGEND:7:' . Config::get('mono_font') . ' --font AXIS:6:' . Config::get('mono_font') . ' ';
} else {
    $rrd_cmd .= ' --font LEGEND:8:' . Config::get('mono_font') . ' --font AXIS:7:' . Config::get('mono_font') . ' ';
}

if (isset($_GET['debug'])) {
    header('Content-Type: text/plain; charset=utf-8');
    printf("Would have executed:\n%s\n", $rrd_cmd);

    return 0;
} elseif ($rrd_cmd) {
    header('Content-Type: image/png');
    header('Cache-Control: max-age=60');
    $rt = 0;
    passthru($rrd_cmd, $rt);
    if ($rt !== 0) {
        return error500($graph_identifier, 'RRD failed to generate the graph: ' . $rt);
    }

    return $rt;
} else {
    return error500($graph_identifier, 'Failed to tell RRD how to generate the graph');
}
