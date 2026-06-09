<?php

/**
 * Zynos.php
 *
 * -Description-
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 * @copyright  2025 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\PortsFdb;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\FdbTableDiscovery;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS\Shared\Zyxel;
use SnmpQuery;

class Zynos extends Zyxel implements OSDiscovery, FdbTableDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        // if not already set, let's fill the gaps
        if (empty($device->hardware)) {
            $device->hardware = $device->sysDescr;
        }

        if (empty($device->serial)) {
            $serial_oids = [
                '.1.3.6.1.4.1.890.1.5.8.20.1.10.0', // ZYXEL-GS4012F-MIB::sysSerialNumber.0
                '.1.3.6.1.4.1.890.1.5.8.47.1.10.0', // ZYXEL-MGS3712-MIB::sysSerialNumber.0
                '.1.3.6.1.4.1.890.1.5.8.55.1.10.0', // ZYXEL-GS2200-24-MIB::sysSerialNumber.0
            ];
            $serials = snmp_get_multi_oid($this->getDeviceArray(), $serial_oids);

            foreach ($serial_oids as $oid) {
                if (! empty($serials[$oid])) {
                    $device->serial = $serials[$oid];
                    break;
                }
            }
        }
    }

    public function discoverFdbTable(): Collection
    {
        $fdbt = new Collection;

        if (Str::contains($this->getDeviceArray()['hardware'], 'GS1900')) {
            //will match anything starting with GS1900 before the 1st dash (like GS1900-8, GS1900-24E etc etc)
            echo 'Zyxel buggy Q-BRIDGE:' . PHP_EOL;
            // These devices do not provide a proper Q-BRIDGE reply (there is a ".6." index between VLAN and MAC)
            // <vlanid>.6.<mac1>.<mac2>.<mac3>.<mac4>.<mac5>.<mac6>
            // We need to manually handle this here

            $fdbPort_table = SnmpQuery::hideMib()->numericIndex()->walk('Q-BRIDGE-MIB::dot1qTpFdbPort')->valuesByIndex();
            foreach ($fdbPort_table as $index => $port_data) {
                // Let's remove the wrong data in the index

                // We'll assume that 1st element is vlan, and last 6 are mac. This will remove the '6' in between them and be safe in case they
                // fix the Q-BRIDGE implementation
                $indexes = explode('.', (string) $index);
                $lastSix = array_slice($indexes, -6); // Extract the last 6 elements from the array
                $hexParts = array_map(fn ($i) => dechex((int) $i), $lastSix); // Convert each element to hexadecimal
                $mac_address = implode(':', $hexParts); // Join them into a MAC address string
                $fdbt->push(new PortsFdb([
                    'port_id' => PortCache::getIdFromIfIndex($port_data['dot1qTpFdbPort'] ?? 0, $this->getDeviceId()),
                    'mac_address' => $mac_address,
                    'vlan_id' => $vlan = $indexes[0], //1st element
                ]));
            }
        }

        if ($fdbt->isEmpty()) {
            $fdbt = parent::discoverFdbTable();
        }

        return $fdbt;
    }
}
