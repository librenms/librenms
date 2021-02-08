<?php
/**
 * AlcomaAlmp.php
 *
 * Alcoma.cz
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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class AlcomaAlmp extends OS implements
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery
{
    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return [
            // ALCOMA-MIB::alMPTuneTX.0
            new WirelessSensor('frequency', $this->getDeviceId(), '.1.3.6.1.4.1.12140.2.3.1.0', 'alcoma-tx', 1, 'TX Frequency', null, 1, 1),
            // ALCOMA-MIB::alMPTuneRX.0
            new WirelessSensor('frequency', $this->getDeviceId(), '.1.3.6.1.4.1.12140.2.3.2.0', 'alcoma-rx', 1, 'RX Frequency', null, 1, 1),
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
        return [
            // ALCOMA-MIB::alMPTX-PWR.0
            new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.12140.2.3.3.0', 'alcoma-pow-cur', 1, 'Tx Power Current'),
            // ALCOMA-MIB::alMPPTX.0
            new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.12140.2.3.13.0', 'alcoma-pow-conf', 1, 'Tx Power Configured'),
        ];
    }

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.12140.2.3.4.0'; // ALCOMA-MIB::alMPRX-Level.0

        return [
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'alcoma', 1, 'RSSI', null, 1, 1),
        ];
    }

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        $oid = '.1.3.6.1.4.1.12140.2.4.2.0'; // ALCOMA-MIB::alMPSNR.0

        return [
            new WirelessSensor('snr', $this->getDeviceId(), $oid, 'alcoma', 1, 'CINR', null, 1, 1),
        ];
    }
}
