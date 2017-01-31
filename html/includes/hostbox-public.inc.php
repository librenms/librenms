<?php

/*
 * This file is part of LibreNMS
 *
 * Copyright (c) 2014 Bohdan Sanders <http://bohdans.com/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version. Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($bg == $list_colour_b) {
    $bg = $list_colour_a;
} else {
    $bg = $list_colour_b;
}

if ($device['status'] == '0') {
    $class = 'bg-danger';
} else {
    $class = 'bg-primary';
}

if ($device['ignore'] == '1') {
    $class = 'bg-warning';
    if ($device['status'] == '1') {
        $class = 'bg-success';
    }
}

if ($device['disabled'] == '1') {
    $class = 'bg-info';
}

$type = strtolower($device['os']);

if ($device['os'] == 'ios') {
    formatCiscoHardware($device, true);
}

$device['os_text'] = $config['os'][$device['os']]['text'];

echo '  <tr>
          <td class="'.$class.' "></td>
          <td>'.$image.'</td>
          <td><span style="font-size: 15px;">'.generate_device_link($device).'</span></td>';

echo '<td>';
if ($port_count) {
    echo ' <i class="fa fa-link fa-lg icon-theme" aria-hidden="true"></i> '.$port_count;
}

echo '<br />';
if ($sensor_count) {
    echo ' <i class="fa fa-tachometer fa-lg icon-theme" aria-hidden="true"></i> '.$sensor_count;
}

echo '</td>';
echo '    <td>'.$device['hardware'].' '.$device['features'].'</td>';
echo '    <td>'.formatUptime($device['uptime'], 'short').' <br />';

echo '    '.substr($device['location'], 0, 32).'</td>';

echo ' </tr>';
