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

use App\Models\Port;

unset($icon);
$severity_colour = eventlog_severity($entry['severity']);
$icon = '<span class="alert-status ' . $severity_colour . '"></span>';

echo '<tr>';
echo '<td>' . $icon . '</td>';
echo '<td>' . $entry['humandate'] . '</td>';

echo '<td style="white-space: nowrap;max-width: 100px;overflow: hidden;text-overflow: ellipsis;">';

if ($entry['type'] == 'interface') {
    echo '<b>' . \LibreNMS\Util\Url::portLink(Port::find($entry['reference'])) . '</b>';
}

echo '</td><td>' . htmlspecialchars($entry['message']) . '</td>';

echo '</tr>';
