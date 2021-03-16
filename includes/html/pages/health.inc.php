<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

$no_refresh = true;

$datas = ['mempool', 'processor', 'storage'];

$used_sensors = \LibreNMS\Util\ObjectCache::sensors();
foreach ($used_sensors as $group => $types) {
    foreach ($types as $entry) {
        $datas[] = $entry['class'];
    }
}

$type_text = [
    'overview' => 'Overview',
    'temperature' => 'Temperature',
    'charge' => 'Battery Charge',
    'humidity' => 'Humidity',
    'mempool' => 'Memory',
    'storage' => 'Storage',
    'diskio' => 'Disk I/O',
    'processor' => 'Processor',
    'voltage' => 'Voltage',
    'fanspeed' => 'Fanspeed',
    'frequency' => 'Frequency',
    'runtime' => 'Runtime',
    'current' => 'Current',
    'power' => 'Power',
    'power_consumed' => 'Power Consumed',
    'power_factor' => 'Power Factor',
    'dbm' => 'dBm',
    'load' => 'Load',
    'loss' => 'Loss',
    'state' => 'State',
    'count' => 'Count',
    'signal' => 'Signal',
    'tv_signal' => 'TV signal',
    'snr' => 'SNR',
    'pressure' => 'Pressure',
    'cooling' => 'Cooling',
    'toner' => 'Toner',
    'delay' => 'Delay',
    'quality_factor' => 'Quality factor',
    'chromatic_dispersion' => 'Chromatic Dispersion',
    'ber' => 'Bit Error Rate',
    'eer' => 'Energy Efficiency Ratio',
    'waterflow' => 'Water Flow Rate',
    'percent' => 'Percent',
];

$active_metric = basename($vars['metric'] ?? 'processor');

if (! $vars['view']) {
    $vars['view'] = 'detail';
}

$link_array = ['page' => 'health'];

$navbar = '<span style="font-weight: bold;">Health</span> &#187; ';
$sep = '';
foreach ($datas as $texttype) {
    $metric = strtolower($texttype);
    $navbar .= $sep;
    if ($active_metric == $metric) {
        $navbar .= '<span class="pagemenu-selected">';
    }
    $navbar .= generate_link($type_text[$metric], $link_array, ['metric'=> $metric, 'view' => $vars['view']]);
    if ($active_metric == $metric) {
        $navbar .= '</span>';
    }
    $sep = ' | ';
}
unset($sep);

if ($vars['view'] == 'graphs') {
    $displayoptions = '<span class="pagemenu-selected">';
}

$displayoptions .= generate_link('Graphs', $link_array, ['metric'=> $active_metric, 'view' => 'graphs']);

if ($vars['view'] == 'graphs') {
    $displayoptions .= '</span>';
}

$displayoptions .= ' | ';

if ($vars['view'] != 'graphs') {
    $displayoptions .= '<span class="pagemenu-selected">';
}

$displayoptions .= generate_link('No Graphs', $link_array, ['metric'=> $active_metric, 'view' => 'detail']);

if ($vars['view'] != 'graphs') {
    $displayoptions .= '</span>';
}

if (in_array($active_metric, $datas)) {
    include "includes/html/pages/health/$active_metric.inc.php";
} else {
    echo "No sensors of type $active_metric found.";
}
