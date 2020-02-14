<?php
/**
 * aos.inc.php
 *
 * Discover FDB data with ALCATEL-IND1-MAC-ADDRESS-MIB
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
 * @package   LibreNMS
 * @link      http://librenms.org
 * @copyright LibreNMS contributors
 * @author    Tony Murray <murraytony@gmail.com>
 * @author    JoseUPV
 */

// Try nokia/aos7/ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressGblManagement first
$dot1d = snmpwalk_group($device, 'slMacAddressGblManagement', 'ALCATEL-IND1-MAC-ADDRESS-MIB', 0, array(), 'nokia/aos7');
if (!empty($dot1d)) {
    echo 'AOS7+ MAC-ADDRESS-MIB:';
    $fdbPort_table=array();
    foreach ($dot1d['slMacAddressGblManagement'] as $slMacDomain => $data) {
        foreach ($data as $slLocaleType => $data2) {
            foreach ($data2 as $portLocal => $data3) {
                foreach ($data3 as $vlanLocal => $data4) {
                    $fdbPort_table[$vlanLocal]=array('dot1qTpFdbPort' => array_combine(array_keys($data4[0]), array_fill(0, count($data4[0]), $portLocal)));
                }
            }
        }
    }
} else {
    // try nokia/ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressDisposition
    $dot1d = snmpwalk_group($device, 'slMacAddressDisposition', 'ALCATEL-IND1-MAC-ADDRESS-MIB', 0, array(), 'nokia');
    if (!empty($dot1d)) {
        echo 'AOS6 MAC-ADDRESS-MIB: ';
        $fdbPort_table=array();
        foreach ($dot1d['slMacAddressDisposition'] as $portLocal => $data) {
            foreach ($data as $vlanLocal => $data2) {
                $fdbPort_table[$vlanLocal]=array('dot1qTpFdbPort' => array_combine(array_keys($data2), array_fill(0, count($data2), $portLocal)));
            }
        }
    }
}
if (!empty($fdbPort_table)) {
    // Build dot1dBasePort to port_id dictionary
    $portid_dict = array();
    $dot1dBasePortIfIndex = snmpwalk_group($device, 'dot1dBasePortIfIndex', 'BRIDGE-MIB');
    foreach ($dot1dBasePortIfIndex as $portLocal => $data) {
        $port = get_port_by_index_cache($device['device_id'], $data['dot1dBasePortIfIndex']);
        $portid_dict[$port['ifIndex']] = $port['port_id'];
    }
    // Collect data and populate $insert
    foreach ($fdbPort_table as $vlan => $data) {
        foreach ($data['dot1qTpFdbPort'] as $mac => $dot1dBasePort) {
            if ($dot1dBasePort == 0) {
                d_echo("No port known for $mac\n");
                continue;
            }
            $mac_address = implode(array_map('zeropad', explode(':', $mac)));
            if (strlen($mac_address) != 12) {
                d_echo("MAC address padding failed for $mac\n");
                continue;
            }
            $port_id = $portid_dict[$dot1dBasePort];
            $vlan_id = isset($vlans_dict[$vlan]) ? $vlans_dict[$vlan] : 0;
            $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
            d_echo("vlan $vlan_id mac $mac_address port ($dot1dBasePort) $port_id\n");
        }
    }
}

echo PHP_EOL;
