<?php
/**
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * Display relative QFP utilization (in %) to kpps processed
 *
 * @link       https://www.librenms.org
 * @copyright  2019 LibreNMS
 * @author     Pavle Obradovic <pobradovic08@gmail.com>
 */
$colour_line = 'cc0000';
$colour_area = 'FFBBBB';
$colour_minmax = 'c5c5c5';

$graph_max = 1;
$line_text = $components['name'];
include 'includes/html/graphs/common.inc.php';

$line_text = str_pad(substr($line_text, 0, 12), 12);

$rrd_options .= ' DEF:in_pkts=' . $rrd_filename . ':InTotalPps:AVERAGE';
$rrd_options .= ' DEF:out_pkts=' . $rrd_filename . ':OutTotalPps:AVERAGE';
$rrd_options .= ' DEF:load=' . $rrd_filename . ':ProcessingLoad:AVERAGE';
$rrd_options .= ' CDEF:total_kpps=in_pkts,out_pkts,+,1000,/';
$rrd_options .= ' CDEF:relative=load,total_kpps,/';

$rrd_options .= ' AREA:relative#' . $colour_area . ':';
$rrd_options .= " COMMENT:'Load % per 1kpps'\\n";
$rrd_options .= ' LINE1.25:relative#' . $colour_line . ":'" . $line_text . "'";
$rrd_options .= ' COMMENT:\\n';
