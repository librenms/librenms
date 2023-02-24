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

$hostname = gethostbyid($entry['device_id']);

unset($icon);

$severity_colour = eventlog_severity($entry['severity']);
$icon = '<span class="alert-status ' . $severity_colour . '"></span>';

echo '<tr>';
echo '<td>' . $icon . '</td>';
echo '<td style="vertical-align: middle;">' . $entry['datetime'] . '</td>';

if (! isset($vars['device'])) {
    $dev = device_by_id_cache($entry['device_id']);
    echo '<td style="vertical-align: middle;">' . generate_device_link($dev, shorthost($dev['hostname'])) . '</td>';
}

if ($entry['type'] == 'interface') {
    $this_if = cleanPort(getifbyid($entry['reference']));
    $entry['link'] = '<b>' . generate_port_link($this_if, makeshortif(strtolower($this_if['label']))) . '</b>';
} else {
    $entry['link'] = 'System';
}

echo '<td style="vertical-align: middle;">' . $entry['link'] . '</td>';

echo '<td style="vertical-align: middle;">' . htmlspecialchars($entry['message']) . '</td>';
echo '</tr>';
