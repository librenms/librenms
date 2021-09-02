<?php
/**
 * Ignitenet.php
 *
 * Ignitenet HeliOS
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;

class Helios extends OS implements WirelessFrequencyDiscovery, WirelessPowerDiscovery, WirelessRssiDiscovery
{
    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return $this->discoverOid('frequency', 'mlRadioInfoFrequency', '.1.3.6.1.4.1.47307.1.4.2.1.4.');
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        return $this->discoverOid('power', 'mlRadioInfoTxPower', '.1.3.6.1.4.1.47307.1.4.2.1.7.');
    }

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        return $this->discoverOid('rssi', 'mlRadioInfoRSSILocal', '.1.3.6.1.4.1.47307.1.4.2.1.10.');
    }

    private function discoverOid($type, $oid, $oid_prefix)
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), $oid, [], 'IGNITENET-MIB');

        $sensors = [];
        foreach ($oids as $index => $data) {
            $sensors[] = new WirelessSensor(
                $type,
                $this->getDeviceId(),
                $oid_prefix . $index,
                'ignitenet',
                $index,
                "Radio $index",
                $data[$oid]
            );
        }

        return $sensors;
    }
}
