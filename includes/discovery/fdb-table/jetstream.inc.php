<?php
/**
 * jetstream.inc.php
 *
 * Discover FDB data with Q-BRIDGE-MIB for Jetstream switches
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
 * @copyright  2022 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$oids = SnmpQuery::allowUnordered()->hideMib()->walk('Q-BRIDGE-MIB::dot1qTpFdbPort')->table(2);
if (! empty($oids)) {
    $insert = [];
    d_echo('Jetstream: FDB Table');
    foreach ($oids as $vlan => $oidData) {
        foreach ($oidData as $mac => $macData) {
            $port = $macData['dot1qTpFdbPort'];
            //try both variation with & without space
            $port_id = find_port_id('gigabitEthernet 1/0/' . $port, 'gigabitEthernet1/0/' . $port, $device['device_id']) ?? 0;
            $mac_address = implode(array_map('zeropad', explode(':', $mac)));
            if (strlen($mac_address) != 12) {
                d_echo("MAC address padding failed for $mac\n");
                continue;
            }
            $vlan_id = $vlans_dict[$vlan] ?? 0;
            $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
            d_echo("vlan $vlan_id mac $mac_address port $port_id\n");
        }
    }
}

echo PHP_EOL;
