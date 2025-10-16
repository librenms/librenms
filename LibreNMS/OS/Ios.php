<?php

/**
 * Ios.php
 *
 * Cisco IOS
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCellDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessChannelDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Polling\PortSecurityPolling;
use LibreNMS\OS\Shared\Cisco;
use LibreNMS\OS\Traits\CiscoCellular;
use LibreNMS\OS\Traits\CiscoPortSecurity;

class Ios extends Cisco implements
    WirelessCellDiscovery,
    WirelessChannelDiscovery,
    WirelessClientsDiscovery,
    WirelessRssiDiscovery,
    WirelessRsrqDiscovery,
    WirelessRsrpDiscovery,
    WirelessSnrDiscovery,
    PortSecurityPolling
{
    use CiscoCellular;
    use CiscoPortSecurity;

    /**
     * @return WirelessSensor[] Sensors
     */
    public function discoverWirelessClients(): array
    {
        $device = $this->getDevice();

        if (empty($device->hardware) || (! str_starts_with($device->hardware, 'AIR-') && ! str_contains($device->hardware, 'ciscoAIR'))) {
            // unsupported IOS hardware
            return [];
        }

        $data = \SnmpQuery::walk('CISCO-DOT11-ASSOCIATION-MIB::cDot11ActiveWirelessClients')->table(1);

        if (empty($data)) {
            return [];
        }

        $this->mapToEntPhysical($data);

        $sensors = [];
        foreach ($data as $ifIndex => $entry) {
            $sensors[] = new WirelessSensor(
                'clients',
                $device['device_id'],
                ".1.3.6.1.4.1.9.9.273.1.1.2.1.1.$ifIndex",
                'ios',
                $ifIndex,
                $entry['entPhysicalDescr'],
                $entry['cDot11ActiveWirelessClients'],
                1,
                1,
                'sum',
                null,
                40,
                null,
                30,
                $entry['entPhysicalIndex'],
                'ports'
            );
        }

        return $sensors;
    }

    private function mapToEntPhysical(array &$data): array
    {
        // try DB first
        $dbMap = $this->getDevice()->entityPhysical;
        if ($dbMap->isNotEmpty()) {
            foreach ($data as $ifIndex => $_unused) {
                foreach ($dbMap as $entPhys) {
                    if ($entPhys->ifIndex === $ifIndex) {
                        $data[$ifIndex]['entPhysicalIndex'] = $entPhys->entPhysicalIndex;
                        $data[$ifIndex]['entPhysicalDescr'] = $entPhys->entPhysicalDescr;
                        break;
                    }
                }
            }

            return $data;
        }

        $entPhys = \SnmpQuery::walk('ENTITY-MIB::entPhysicalDescr')->table(1);

        // fixup incorrect/missing entPhysicalIndex mapping (doesn't use entAliasMappingIdentifier for some reason)
        foreach ($data as $ifIndex => $_unused) {
            foreach ($entPhys as $entIndex => $ent) {
                $descr = $ent['ENTITY-MIB::entPhysicalDescr'];
                unset($entPhys[$entIndex]); // only use each one once

                if (str_ends_with($descr, 'Radio')) {
                    d_echo("Mapping entPhysicalIndex $entIndex to ifIndex $ifIndex\n");
                    $data[$ifIndex]['entPhysicalIndex'] = $entIndex;
                    $data[$ifIndex]['entPhysicalDescr'] = $descr;
                    break;
                }
            }
        }

        return $data;
    }
}
