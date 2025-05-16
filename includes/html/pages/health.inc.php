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

$type_text = [
    'overview' => 'Overview',
    'mempool' => 'Memory',
    'storage' => 'Storage',
    'diskio' => 'Disk I/O',
    'processor' => 'Processor',
];

$datas = ['mempool', 'processor', 'storage'];

$used_sensors = \LibreNMS\Util\ObjectCache::sensors();
foreach ($used_sensors as $group => $types) {
    foreach ($types as $entry) {
        $datas[] = $entry['class'];
        $type_text[$entry['class']] = trans('sensors.' . $entry['class'] . '.short');
    }
}

$active_metric = basename(array_key_exists($vars['metric'], $type_text) ? $vars['metric'] : 'processor');

$vars['view'] = $vars['view'] ?? 'detail';
$link_array = ['page' => 'health'];

$navbar = '<span style="font-weight: bold;">Health</span> &#187; ';
$sep = '';
foreach ($datas as $texttype) {
    $metric = strtolower($texttype);
    $navbar .= $sep;
    if ($active_metric == $metric) {
        $navbar .= '<span class="pagemenu-selected">';
    }
    $navbar .= generate_link($type_text[$metric], $link_array, ['metric' => $metric, 'view' => $vars['view']]);
    if ($active_metric == $metric) {
        $navbar .= '</span>';
    }
    $sep = ' | ';
}
unset($sep);

$displayoptions = '';
if ($vars['view'] == 'graphs') {
    $displayoptions = '<span class="pagemenu-selected">';
}

$displayoptions .= generate_link('Graphs', $link_array, ['metric' => $active_metric, 'view' => 'graphs']);

if ($vars['view'] == 'graphs') {
    $displayoptions .= '</span>';
}

$displayoptions .= ' | ';

if ($vars['view'] != 'graphs') {
    $displayoptions .= '<span class="pagemenu-selected">';
}

$displayoptions .= generate_link('No Graphs', $link_array, ['metric' => $active_metric, 'view' => 'detail']);

if ($vars['view'] != 'graphs') {
    $displayoptions .= '</span>';
}

if (in_array($active_metric, $datas)) {
    include "includes/html/pages/health/$active_metric.inc.php";
} else {
    echo "No sensors of type $active_metric found.";
}
