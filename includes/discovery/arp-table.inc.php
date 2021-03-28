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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\Config;

if (key_exists('vrf_lite_cisco', $device) && (count($device['vrf_lite_cisco']) != 0)) {
    $vrfs_lite_cisco = $device['vrf_lite_cisco'];
} else {
    $vrfs_lite_cisco = [['context_name'=>'']];
}

foreach ($vrfs_lite_cisco as $vrf) {
    $context = $vrf['context_name'];
    $device['context_name'] = $context;

    if (file_exists(Config::get('install_dir') . "/includes/discovery/arp-table/{$device['os']}.inc.php")) {
        include Config::get('install_dir') . "/includes/discovery/arp-table/{$device['os']}.inc.php";
    } else {
        $arp_data = snmpwalk_group($device, 'ipNetToPhysicalPhysAddress', 'IP-MIB');
        $arp_data = snmpwalk_group($device, 'ipNetToMediaPhysAddress', 'IP-MIB', 1, $arp_data);
    }

    $sql = 'SELECT * from `ipv4_mac` WHERE `device_id`=? AND `context_name`=?';
    $existing_data = dbFetchRows($sql, [$device['device_id'], $context]);

    $ipv4_addresses = array_map(function ($data) {
        return $data['ipv4_address'];
    }, $existing_data);
    $arp_table = [];
    $insert_data = [];
    foreach ($arp_data as $ifIndex => $data) {
        $interface = get_port_by_index_cache($device['device_id'], $ifIndex);
        $port_id = $interface['port_id'];

        $port_arp = array_merge(
            (array) $data['ipNetToMediaPhysAddress'],
            is_array($data['ipNetToPhysicalPhysAddress']) ? (array) $data['ipNetToPhysicalPhysAddress']['ipv4'] : []
        );

        echo "{$interface['ifName']}: \n";
        foreach ($port_arp as $ip => $raw_mac) {
            if (empty($ip) || empty($raw_mac) || $raw_mac == '0:0:0:0:0:0' || isset($arp_table[$port_id][$ip])) {
                continue;
            }

            $mac = implode(array_map('zeropad', explode(':', $raw_mac)));
            $arp_table[$port_id][$ip] = $mac;

            $index = array_search($ip, $ipv4_addresses);
            if ($index !== false) {
                $old_mac = $existing_data[$index]['mac_address'];
                if ($mac != $old_mac && $mac != '') {
                    d_echo("Changed mac address for $ip from $old_mac to $mac\n");
                    log_event("MAC change: $ip : " . \LibreNMS\Util\Rewrite::readableMac($old_mac) . ' -> ' . \LibreNMS\Util\Rewrite::readableMac($mac), $device, 'interface', 4, $port_id);
                    dbUpdate(['mac_address' => $mac], 'ipv4_mac', 'port_id=? AND ipv4_address=? AND context_name=?', [$port_id, $ip, $context]);
                }
                d_echo("$raw_mac => $ip\n", '.');
            } elseif (isset($interface['port_id'])) {
                d_echo("$raw_mac => $ip\n", '+');
                $insert_data[] = [
                    'port_id'      => $port_id,
                    'device_id'    => $device['device_id'],
                    'mac_address'  => $mac,
                    'ipv4_address' => $ip,
                    'context_name' => (string) $context,
                ];
            }
        }
        echo PHP_EOL;
    }

    unset(
        $interface,
        $arp_data,
        $ipv4_addresses,
        $data
    );

    // add new entries
    if (! empty($insert_data)) {
        dbBulkInsert($insert_data, 'ipv4_mac');
    }

    // remove stale entries
    foreach ($existing_data as $entry) {
        $entry_mac = $entry['mac_address'];
        $entry_if = $entry['port_id'];
        $entry_ip = $entry['ipv4_address'];
        if ($arp_table[$entry_if][$entry_ip] != $entry_mac) {
            dbDelete('ipv4_mac', '`port_id` = ? AND `mac_address`=? AND `ipv4_address`=? AND `context_name`=?', [$entry_if, $entry_mac, $entry_ip, $context]);
            d_echo(null, '-');
        }
    }

    // remove entries that no longer have an owner
    dbDeleteOrphans('ipv4_mac', ['ports.port_id', 'devices.device_id']);

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
