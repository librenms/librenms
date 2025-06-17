<?php

/**
 * Draytek.php
 *
 * DrayTek OS
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
 * @copyright  2025 CTNET BV
 * @author     Rudy Broersma <r.broersma@ctnet.nl>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Draytek extends OS implements WirelessRssiDiscovery
{

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $lte_modems = SnmpQuery::walk('DRAYTEK-MIB::lterssi')->table(1);
        $oid = '.1.3.6.1.4.1.7367.4.7.'; // DRAYTEK-MIB::lterssi
        $sensors = [];

        foreach ($lte_modems as $index => $modem) {
            $sensors[] = new WirelessSensor('rssi', $this->getDeviceId(), $oid . $index, 'draytek-lte', $index, 'RSSI', null, 1, 1);
        }

        return $sensors;
    }
}
