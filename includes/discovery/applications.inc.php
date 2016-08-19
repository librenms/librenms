<?php
/**
 * applications.inc.php
 *
 * Discover applications
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

$oid = '.1.3.6.1.4.1.8072.1.3.2.2.1.21'; // NET-SNMP-EXTEND-MIB::nsExtendStatus
$results = snmpwalk_cache_oid($device, $oid, array());

$applications = array();
if ($results) {
    // Load our list of available applications
    foreach (scandir($config['install_dir'] . '/includes/polling/applications/') as $file) {
        if (substr($file, -8) == '.inc.php') {
            $name = substr($file, 0, -8);
            $applications[$name] = $name;
        }
    }

    // fix applications that don't match their snmp extend name
    $applications['osupdates'] = 'os-updates';
}

d_echo('Available: ' . implode(', ', array_keys($applications)) . "\n");
d_echo('Checking for: ' . implode(', ', array_keys($results)) . "\n");

echo 'Applications: ';

foreach ($results as $extend => $result) {
    if (isset($applications[$extend])) {
        $app = $applications[$extend];
        echo "$app ";

        dbInsert(array(
            'device_id' => $device['device_id'],
            'app_type' => $app,
            'app_status' => '',
            'app_instance' => ''
        ), 'applications');
    }

}