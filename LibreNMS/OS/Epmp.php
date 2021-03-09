<?php
/**
 * Epmp.php
 *
 * Cambium
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
 * @copyright  2017 Paul Heinrichs
 * @author     Paul Heinrichs<pdheinrichs@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Epmp extends OS implements
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessFrequencyDiscovery,
    WirelessClientsDiscovery
{
    /**
     * Discover wireless bit/packet error ratio.  This is in percent. Type is error-ratio.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRssi()
    {
        $rssi_oid = '.1.3.6.1.4.1.17713.21.1.2.3.0'; //CAMBIUM-PMP80211-MIB::cambiumSTADLRSSI.0

        return [
            new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                $rssi_oid,
                'epmp',
                0,
                'Cambium ePMP RSSI',
                null
            ),
        ];
    }

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Formula: SNR = Signal or Rx Power - Noise Floor
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        $snr = '.1.3.6.1.4.1.17713.21.1.2.18.0'; //CAMBIUM-PMP80211-MIB::cambiumSTADLSNR.0

        return [
            new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                $snr,
                'epmp',
                0,
                'Cambium ePMP SNR',
                null
            ),
        ];
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $frequency = '.1.3.6.1.4.1.17713.21.1.2.1.0'; //CAMBIUM-PMP80211-MIB::cambiumSTAConnectedRFFrequency"

        return [
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $frequency,
                'epmp',
                0,
                'Cambium ePMP Frequency',
                null
            ),
        ];
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $registeredSM = '.1.3.6.1.4.1.17713.21.1.2.10.0'; //CAMBIUM-PMP80211-MIB::cambiumAPNumberOfConnectedSTA.0

        return [
            new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $registeredSM,
                'epmp',
                0,
                'Client Count',
                null
            ),
        ];
    }
}
