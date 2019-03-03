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
 * @copyright  2019 LibreNMS
 * @author     LibreNMS Contributors
*/

echo '<tr class="list">';
echo '<td class="list">';

if (getidbyname($vm['vmwVmDisplayName'])) {
    echo generate_device_link(device_by_name($vm['vmwVmDisplayName']));
} else {
    echo $vm['vmwVmDisplayName'];
}

echo '</td>';

if ($vm['vmwVmState'] == 'powered off') {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-danger">OFF</span></td>';
} else {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-success">ON</span></td>';
}

if ($vm['vmwVmGuestOS'] == 'E: tools not installed') {
    echo '<td class="box-desc"><span class="label label-warning">Unknown (VMware Tools not installed)</span></td>';
} elseif ($vm['vmwVmGuestOS'] == '') {
    echo '<td class="box-desc"><span class="label label-danger">(Unknown)</span></td>';
} elseif (isset($config['vmware_guestid'][$vm['vmwVmGuestOS']])) {
    echo '<td class="list">' . $config['vmware_guestid'][$vm['vmwVmGuestOS']] . '</td>';
} else {
    echo '<td class="list">' . $vm['vmwVmGuestOS'] . '</td>';
}

if ($vm['vmwVmMemSize'] >= 1024) {
    echo('<td class=list>' . sprintf('%.2f', ($vm['vmwVmMemSize'] / 1024)) . ' GB</td>');
} else {
    echo '<td class=list>' . sprintf('%.2f', $vm['vmwVmMemSize']) . ' MB</td>';
}

echo '<td class="list">' . $vm['vmwVmCpus'] . ' CPU</td>';
