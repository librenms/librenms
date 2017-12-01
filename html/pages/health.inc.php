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

if (!$vars['metric']) {
    $vars['metric'] = "processor";
}
if (!$vars['view']) {
    $vars['view'] = "detail";
}

$link_array = array('page'    => 'health');

$displayoptions = '<div style="float: right;">';

if ($vars['view'] == "graphs") {
    $displayoptions .= '<span class="pagemenu-selected">';
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

$displayoptions .= '</div>';

if (in_array($vars['metric'], array_keys($used_sensors))
    || $vars['metric'] == 'processor'
    || $vars['metric'] == 'storage'
    || $vars['metric'] == 'toner'
    || $vars['metric'] == 'mempool') {
    include('pages/health/'.$vars['metric'].'.inc.php');
} else {
    echo("No sensors of type " . $vars['metric'] . " found.");
}
