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

use App\Models\Sensor;
use LibreNMS\Util\ObjectCache;

$no_refresh = true;

$datas = collect(['mempool', 'processor', 'storage'])
    ->merge(collect(ObjectCache::sensors())
        ->flatMap(fn ($types) => collect($types)->pluck('class'))
    );

$type_text = collect([
    'overview' => __('Overview'),
    'temperature' => __('Temperature'),
    'mempool' => __('Memory'),
    'storage' => __('Storage'),
    'diskio' => __('Disk I/O'),
    'processor' => __('Processor'),
    'toner' => __('Toner'),
])->merge(collect(Sensor::getTypes())
      ->mapWithKeys(fn ($type) => [$type => Sensor::getClassDescr($type)])
)->toArray();

$active_metric = basename(array_key_exists($vars['metric'], $type_text) ? $vars['metric'] : 'processor');

$vars['view'] = $vars['view'] ?? 'detail';
$link_array = ['page' => 'health'];

$navbar = '<span style="font-weight: bold;">' . __('Health') . '</span> &#187; ';
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

if ($datas->contains($active_metric)) {
    if(file_exists('includes/html/pages/health/' . $active_metric . '.inc.php')){
        include 'includes/html/pages/health/' . $active_metric . '.inc.php';
    }else{
        include 'includes/html/pages/health/sensors.inc.php';
    }
} else {
    echo 'No sensors of type ' . $active_metric . ' found.';
}
