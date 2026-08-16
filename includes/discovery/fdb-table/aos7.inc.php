<?php

/**
 * aos7.inc.php
 *
 * Discover FDB data with ALCATEL-IND1-MAC-ADDRESS-MIB (AOS7+)
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
 * @link      https://www.librenms.org
 *
 * @copyright LibreNMS contributors
 * @author    Tony Murray <murraytony@gmail.com>
 * @author    JoseUPV
 * @author    Paul Iercosan <mail@paulierco.ro>
 */

use App\Facades\PortCache;
use LibreNMS\Util\Mac;

echo 'AOS7+ MAC-ADDRESS-MIB: ';

$dot1d = snmpwalk_group(
    $device,
    'slMacAddressGblManagement',
    'ALCATEL-IND1-MAC-ADDRESS-MIB',
    0,
    [],
    'nokia/aos7'
);

foreach ($dot1d['slMacAddressGblManagement'] ?? [] as $fids) {
    foreach ($fids as $mappings) {
        foreach ($mappings as $ifIndex => $vlans) {
            $port_id = PortCache::getIdFromIfIndex($ifIndex, $device['device_id']);

            if (! $port_id) {
                continue;
            }

            foreach ($vlans as $vlan => $timeMarks) {
                $vlan_id = $vlans_dict[(int) $vlan] ?? 0;

                if (! $vlan_id) {
                    continue;
                }

                foreach ($timeMarks as $macs) {
                    foreach ($macs as $mac => $basePort) {
                        $mac_address = Mac::parse($mac)->hex();

                        if (strlen($mac_address) === 12) {
                            $insert[$vlan_id][$mac_address]['port_id'] = $port_id;
                        }
                    }
                }
            }
        }
    }
}
