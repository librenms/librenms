<?php
/**
 * Cnpilote.php
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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Cnpilote extends OS implements
    WirelessClientsDiscovery,
    WirelessSnrDiscovery,
    WirelessPowerDiscovery,
    WirelessNoiseFloorDiscovery
{
    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $oid = '.1.3.6.1.4.1.17713.22.1.1.1.14.0'; //CAMBIUM-MIB::cambiumAPTotalClients.0

        return [
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'cnpilot', 1, 'Clients'),
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
        $oid = '.1.3.6.1.4.1.17713.22.1.3.1.11.0'; //CAMBIUM-MIB::cambiumClientSNR.0

        return [
            new WirelessSensor('snr', $this->getDeviceId(), $oid, 'cnpilot', 1, 'SNR'),
        ];
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $oid = '.1.3.6.1.4.1.17713.22.1.2.1.8.0'; //CAMBIUM-MIB::cambiumRadioTransmitPower.0

        return [
            new WirelessSensor('power', $this->getDeviceId(), $oid, 'cnpilot', 1, 'Transmit Power'),
        ];
    }

    /**
     * Discover wireless noise floor. This is in dBm/Hz. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor()
    {
        $oid = '.1.3.6.1.4.1.17713.22.1.2.1.16.0'; //CAMBIUM-MIB::cambiumRadioNoiseFloor.0

        return [
            new WirelessSensor('noise-floor', $this->getDeviceId(), $oid, 'cnpilot', 1, 'Radio Noise Floor'),
        ];
    }
}
