<?php
/**
 * arp-table.php
 *
 * Collect arp table entries from devices and update the database
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

if (key_exists('vrf_lite_cisco', $device) && (count($device['vrf_lite_cisco'])!=0)) {
    $vrfs_lite_cisco = $device['vrf_lite_cisco'];
} else {
    $vrfs_lite_cisco = array(array('context_name'=>null));
}

foreach ($vrfs_lite_cisco as $vrf) {
    $context = $vrf['context_name'];
    $device['context_name']=$context;

    $arp_data = snmpwalk_cache_multi_oid($device, 'ipNetToPhysicalPhysAddress', array(), 'IP-MIB');
    $arp_data = snmpwalk_cache_multi_oid($device, 'ipNetToMediaPhysAddress', $arp_data, 'IP-MIB');

    $sql = "SELECT M.* from ipv4_mac AS M, ports AS I WHERE M.port_id=I.port_id AND I.device_id=? AND M.context_name=?";
    $params = array($device['device_id'], $context);
    $existing_data = dbFetchRows($sql, $params);
    $ipv4_addresses = array();
    foreach ($existing_data as $data) {
        $ipv4_addresses[] = $data['ipv4_address'];
    }

    $arp_table = array();
    $insert_data = array();
    foreach ($arp_data as $ip => $data) {
        if (isset($data['ipNetToPhysicalPhysAddress'])) {
            $raw_mac = $data['ipNetToPhysicalPhysAddress'];
            list($if, $ipv, $ip) = explode('.', $ip, 3);
        } elseif (isset($data['ipNetToMediaPhysAddress'])) {
            $raw_mac = $data['ipNetToMediaPhysAddress'];
            list($if, $ip)  = explode('.', $ip, 2);
            $ipv = 'ipv4';
        }

        $interface = get_port_by_index_cache($device['device_id'], $if);
        $port_id = $interface['port_id'];

        if (!empty($ip) && $ipv === 'ipv4' && !empty($raw_mac) && $raw_mac != '0:0:0:0:0:0' && !isset($arp_table[$port_id][$ip])) {
            $mac = implode(array_map('zeropad', explode(':', $raw_mac)));
            $arp_table[$port_id][$ip] = $mac;

            $index = array_search($ip, $ipv4_addresses);
            if ($index !== false) {
                $old_mac = $existing_data[$index]['mac_address'];
                if ($mac != $old_mac && $mac != '') {
                    d_echo("Changed mac address for $ip from $old_mac to $mac\n");
                    log_event("MAC change: $ip : " . mac_clean_to_readable($old_mac) . ' -> ' . mac_clean_to_readable($mac), $device, 'interface', $port_id);
                    dbUpdate(array('mac_address' => $mac), 'ipv4_mac', 'port_id=? AND ipv4_address=? AND context_name=?', array($port_id, $ip, $context));
                }
                d_echo(null, '.');
            } elseif (isset($interface['port_id'])) {
                d_echo(null, '+');
                $insert_data[] = array(
                    'port_id'      => $port_id,
                    'mac_address'  => $mac,
                    'ipv4_address' => $ip,
                    'context_name' => $context,
                );
            }
        }

        unset(
            $interface
        );
    }

    unset(
        $arp_data,
        $ipv4_addresses,
        $data
    );

    // add new entries
    if (!empty($insert_data)) {
        dbBulkInsert($insert_data, 'ipv4_mac');
    }

    // remove stale entries
    foreach ($existing_data as $entry) {
        $entry_mac = $entry['mac_address'];
        $entry_if  = $entry['port_id'];
        $entry_ip  = $entry['ipv4_address'];
        if ($arp_table[$entry_if][$entry_ip] != $entry_mac) {
            dbDelete('ipv4_mac', '`port_id` = ? AND `mac_address`=? AND `ipv4_address`=? AND `context_name`=?', array($entry_if, $entry_mac, $entry_ip, $context));
            d_echo(null, '-');
        }
    }
    echo PHP_EOL;
    unset(
        $existing_data,
        $arp_table,
        $insert_data,
        $sql,
        $params,
        $context,
        $entry,
        $device['context_name']
    );
}
unset($vrfs_lite_cisco);
