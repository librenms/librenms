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
 * @link       http://librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

$no_refresh = true;

$datas = array('mempool','processor','storage');
if ($used_sensors['temperature']) {
    $datas[] = 'temperature';
}
if ($used_sensors['charge']) {
    $datas[] = 'charge';
}
if ($used_sensors['humidity']) {
    $datas[] = 'humidity';
}
if ($used_sensors['fanspeed']) {
    $datas[] = 'fanspeed';
}
if ($used_sensors['voltage']) {
    $datas[] = 'voltage';
}
if ($used_sensors['frequency']) {
    $datas[] = 'frequency';
}
if ($used_sensors['runtime']) {
    $datas[] = 'runtime';
}
if ($used_sensors['current']) {
    $datas[] = 'current';
}
if ($used_sensors['power']) {
    $datas[] = 'power';
}
if ($used_sensors['dbm']) {
    $datas[] = 'dbm';
}
if ($used_sensors['load']) {
    $datas[] = 'load';
}
if ($used_sensors['state']) {
    $datas[] = 'state';
}
if ($used_sensors['signal']) {
    $datas[] = 'signal';
}
if ($used_sensors['snr']) {
    $datas[] = 'snr';
}
if ($used_sensors['pressure']) {
    $datas[] = 'pressure';
}
if ($used_sensors['cooling']) {
    $datas[] = 'cooling';
}
if ($used_sensors['toner']) {
    $datas[] = 'toner';
}
if ($used_sensors['delay']) {
    $datas[] = 'delay';
}
if ($used_sensors['quality_factor']) {
    $datas[] = 'quality_factor';
}
if ($used_sensors['chromatic_dispersion']) {
    $datas[] = 'chromatic_dispersion';
}
if ($used_sensors['ber']) {
    $datas[] = 'ber';
}
if ($used_sensors['eer']) {
    $datas[] = 'eer';
}
if ($used_sensors['waterflow']) {
    $datas[] = 'waterflow';
}

$type_text['overview'] = "Overview";
$type_text['temperature'] = "Temperature";
$type_text['charge'] = "Battery Charge";
$type_text['humidity'] = "Humidity";
$type_text['mempool'] = "Memory";
$type_text['storage'] = "Storage";
$type_text['diskio'] = "Disk I/O";
$type_text['processor'] = "Processor";
$type_text['voltage'] = "Voltage";
$type_text['fanspeed'] = "Fanspeed";
$type_text['frequency'] = "Frequency";
$type_text['runtime'] = "Runtime";
$type_text['current'] = "Current";
$type_text['power'] = "Power";
$type_text['toner'] = "Toner";
$type_text['dbm'] = "dBm";
$type_text['load'] = "Load";
$type_text['state'] = "State";
$type_text['signal'] = "Signal";
$type_text['snr'] = "SNR";
$type_text['pressure'] = "Pressure";
$type_text['cooling'] = "Cooling";
$type_text['toner'] = 'Toner';
$type_text['delay'] = 'Delay';
$type_text['quality_factor'] = 'Quality factor';
$type_text['chromatic_dispersion'] = 'Chromatic Dispersion';
$type_text['ber'] = 'Bit Error Rate';
$type_text['eer'] = 'Energy Efficiency Ratio';
$type_text['waterflow'] = 'Water Flow Rate';

if (!$vars['metric']) {
    $vars['metric'] = "processor";
}
if (!$vars['view']) {
    $vars['view'] = "detail";
}

$link_array = array('page'    => 'health');

$navbar = '<span style="font-weight: bold;">Health</span> &#187; ';
$sep = "";
foreach ($datas as $texttype) {
    $metric = strtolower($texttype);
    $navbar .= $sep;
    if ($vars['metric'] == $metric) {
        $navbar .= '<span class="pagemenu-selected">';
    }
    $navbar .= generate_link($type_text[$metric], $link_array, array('metric'=> $metric, 'view' => $vars['view']));
    if ($vars['metric'] == $metric) {
        $navbar .= '</span>';
    }
    $sep = ' | ';
}
unset($sep);

if ($vars['view'] == "graphs") {
    $displayoptions = '<span class="pagemenu-selected">';
}

$displayoptions .= generate_link("Graphs", $link_array, array('metric'=> $vars['metric'], 'view' => "graphs"));

if ($vars['view'] == "graphs") {
    $displayoptions .= '</span>';
}

$displayoptions .= ' | ';

if ($vars['view'] != "graphs") {
    $displayoptions .= '<span class="pagemenu-selected">';
}

$displayoptions .= generate_link("No Graphs", $link_array, array('metric'=> $vars['metric'], 'view' => "detail"));

if ($vars['view'] != "graphs") {
    $displayoptions .= '</span>';
}

if (in_array($vars['metric'], array_keys($used_sensors))
    || $vars['metric'] == 'processor'
    || $vars['metric'] == 'storage'
    || $vars['metric'] == 'toner'
    || $vars['metric'] == 'mempool') {
    include('pages/health/'.$vars['metric'].'.inc.php');
} else {
    echo("No sensors of type " . $vars['metric'] . " found.");
}
