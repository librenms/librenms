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
 *
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\Config;
use LibreNMS\Util\Mac;

foreach (DeviceCache::getPrimary()->getVrfContexts() as $context_name) {
    if (file_exists(Config::get('install_dir') . "/includes/discovery/arp-table/{$device['os']}.inc.php")) {
        include Config::get('install_dir') . "/includes/discovery/arp-table/{$device['os']}.inc.php";
    } else {
        $arp_data = SnmpQuery::context($context_name)->walk('IP-MIB::ipNetToPhysicalPhysAddress')->table(1);
        SnmpQuery::context($context_name)->walk('IP-MIB::ipNetToMediaPhysAddress')->table(1, $arp_data);
    }

    $sql = 'SELECT * from `ipv4_mac` WHERE `device_id`=? AND `context_name`=?';
    $existing_data = dbFetchRows($sql, [$device['device_id'], $context_name]);

    $arp_table = [];
    $insert_data = [];
    foreach ($arp_data as $ifIndex => $data) {
        $interface = get_port_by_index_cache($device['device_id'], $ifIndex);
        $port_id = $interface['port_id'];

        $port_arp = array_merge(
            Arr::wrap($data['IP-MIB::ipNetToMediaPhysAddress'] ?? []),
            isset($data['IP-MIB::ipNetToPhysicalPhysAddress']) && is_array($data['IP-MIB::ipNetToPhysicalPhysAddress']) ? (array) $data['IP-MIB::ipNetToPhysicalPhysAddress']['ipv4'] : []
        );

        echo "{$interface['ifName']}: \n";
        foreach ($port_arp as $ip => $raw_mac) {
            $ip = preg_replace('{^\.}', '', $ip, 1);
            if (empty($ip) || empty($raw_mac) || $raw_mac == '0:0:0:0:0:0' || isset($arp_table[$port_id][$ip])) {
                continue;
            }

            $mac = Mac::parse($raw_mac)->hex();
            $arp_table[$port_id][$ip] = $mac;

            $index = false;
            foreach ($existing_data as $existing_key => $existing_value) {
                if ($existing_value['ipv4_address'] == $ip && $existing_value['port_id'] == $port_id) {
                    $index = $existing_key;
                    break;
                }
            }

            if ($index !== false) {
                $old_mac = $existing_data[$index]['mac_address'];
                if ($mac != $old_mac && $mac != '') {
                    d_echo("Changed mac address for $ip from $old_mac to $mac\n");
                    log_event("MAC change: $ip : " . Mac::parse($old_mac)->readable() . ' -> ' . Mac::parse($mac)->readable(), $device, 'interface', 4, $port_id);
                    dbUpdate(['mac_address' => $mac], 'ipv4_mac', 'port_id=? AND ipv4_address=? AND context_name=?', [$port_id, $ip, $context_name]);
                }
                d_echo("$raw_mac => $ip\n", '.');
            } elseif (isset($interface['port_id'])) {
                d_echo("$raw_mac => $ip\n", '+');
                $insert_data[] = [
                    'port_id' => $port_id,
                    'device_id' => $device['device_id'],
                    'mac_address' => $mac,
                    'ipv4_address' => $ip,
                    'context_name' => (string) $context_name,
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
            dbDelete('ipv4_mac', '`port_id` = ? AND `mac_address`=? AND `ipv4_address`=? AND `context_name`=?', [$entry_if, $entry_mac, $entry_ip, $context_name]);
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
        $entry
    );
}
