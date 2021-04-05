<?php
/**
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 LibreNMS
 * @author     Pavle Obradovic <pobradovic08@gmail.com>
 */

/*
 * Get module's components for a device
 */
$component = new LibreNMS\Component();
$components = $component->getComponents($device['device_id'], ['type' => 'cisco-qfp']);
$components = $components[$device['device_id']];

/*
 * Iterate over QFP components and create rrd_list array entry for each of them
 */
$i = 1;
foreach ($components as $component_id => $tmp_component) {
    $rrd_filename = Rrd::name($device['hostname'], ['cisco-qfp', 'util', $tmp_component['entPhysicalIndex']]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $descr = short_hrDeviceDescr($tmp_component['name']);

        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $descr;
        $rrd_list[$i]['ds'] = 'ProcessingLoad';
        $rrd_list[$i]['area'] = 1;
        $i++;
    }
}

$unit_text = 'Util %';

$units = '';
$total_units = '%';
$colours = 'mixed';

$scale_min = '0';
$scale_max = '100';

$nototal = 1;

require 'includes/html/graphs/generic_multi_line.inc.php';
