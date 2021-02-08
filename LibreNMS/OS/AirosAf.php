<?php
/**
 * AirosAf.php
 *
 * Ubiquiti AirFiber
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
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class AirosAf extends OS implements
    OSDiscovery,
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessDistanceDiscovery,
    WirelessRateDiscovery
{
    /**
     * Discover wireless distance.  This is in Kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessDistance()
    {
        $oid = '.1.3.6.1.4.1.41112.1.3.2.1.4.1'; // UBNT-AirFIBER-MIB::radioLinkDistM.1

        return [
            new WirelessSensor('distance', $this->getDeviceId(), $oid, 'airos-af', 1, 'Distance', null, 1, 1000),
        ];
    }

    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $tx_oid = '.1.3.6.1.4.1.41112.1.3.1.1.5.1'; // UBNT-AirFIBER-MIB::txFrequency.1
        $rx_oid = '.1.3.6.1.4.1.41112.1.3.1.1.6.1'; // UBNT-AirFIBER-MIB::rxFrequency.1

        return [
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $tx_oid,
                'airos-af-tx',
                1,
                'Tx Frequency'
            ),
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $rx_oid,
                'airos-af-rx',
                1,
                'Rx Frequency'
            ),
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
        $tx_oid = '.1.3.6.1.4.1.41112.1.3.1.1.9.1'; // UBNT-AirFIBER-MIB::txPower.1
        $rx0_oid = '.1.3.6.1.4.1.41112.1.3.2.1.11.1'; // UBNT-AirFIBER-MIB::rxPower0.1
        $rx1_oid = '.1.3.6.1.4.1.41112.1.3.2.1.14.1'; // UBNT-AirFIBER-MIB::rxPower1.1

        return [
            new WirelessSensor('power', $this->getDeviceId(), $tx_oid, 'airos-af-tx', 1, 'Tx Power'),
            new WirelessSensor('power', $this->getDeviceId(), $rx0_oid, 'airos-af-rx', 0, 'Rx Chain 0 Power'),
            new WirelessSensor('power', $this->getDeviceId(), $rx1_oid, 'airos-af-rx', 1, 'Rx Chain 1 Power'),
        ];
    }

    /**
     * Discover wireless rate. This is in Mbps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $tx_oid = '.1.3.6.1.4.1.41112.1.3.2.1.6.1'; // UBNT-AirFIBER-MIB::txCapacity.1
        $rx_oid = '.1.3.6.1.4.1.41112.1.3.2.1.5.1'; // UBNT-AirFIBER-MIB::rxCapacity.1

        return [
            new WirelessSensor('rate', $this->getDeviceId(), $tx_oid, 'airos-tx', 1, 'Tx Capacity'),
            new WirelessSensor('rate', $this->getDeviceId(), $rx_oid, 'airos-rx', 1, 'Rx Capacity'),
        ];
    }
}
