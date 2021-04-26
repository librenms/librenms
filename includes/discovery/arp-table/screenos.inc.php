<?php
/**
 * screenos.inc.php
 *
 * Juniper ScreenOS arp table support
 * Has a buggy implementation of ipNetToMediaPhysAddress
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

// collect arp data
$nsIpArpTable = snmpwalk_group($device, 'nsIpArpTable', 'NETSCREEN-IP-ARP-MIB');

if (! empty($nsIpArpTable)) {
    // get internal id to ifIndex map
    $nsIfInfo = snmpwalk_group($device, 'nsIfInfo', 'NETSCREEN-INTERFACE-MIB', 0);
    $nsIfInfo = array_flip($nsIfInfo['nsIfInfo']);
}

foreach ($nsIpArpTable as $data) {
    $ifIndex = $nsIfInfo[$data['nsIpArpIfIdx']];
    $arp_data[$ifIndex]['ipNetToMediaPhysAddress'][$data['nsIpArpIp']] = $data['nsIpArpMac'];
}
