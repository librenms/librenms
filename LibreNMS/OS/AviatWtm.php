<?php
/**
 * AviatWtm.php
 *
 * Aviat WTM
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Josh Baird
 * @author     Josh Baird<joshbaird@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;

class AviatWtm extends OS implements
    WirelessFrequencyDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessPowerDiscovery
{
    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */

    public function discoverWirelessFrequency()
    {
        $carrier1_oid = '.1.3.6.1.4.1.2509.9.5.2.1.1.1.59';
        $carrier2_oid = '.1.3.6.1.4.1.2509.9.5.2.1.1.1.60';

        return array(
            new WirelessSensor('frequency', $this->getDeviceId(), $carrier1_oid, 'aviat-wtm-carrier1-tx-freq', 1, 'TX Frequency (Carrier1/1)', null, 1, 1000),
            new WirelessSensor('frequency', $this->getDeviceId(), $carrier2_oid, 'aviat-wtm-carrier2-tx-freq', 1, 'TX Frequency (Carrier1/2)', null, 1, 1000) 
        );
    }

    /**
     * Discover wireless tx or rx capacity. This is in dbps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */

    public function discoverWirelessRate()
    {
        $carrier1_tx_oid = '.1.3.6.1.4.1.2509.9.3.2.1.1.11.59';
        $carrier1_rx_oid = '.1.3.6.1.4.1.2509.9.3.2.1.1.12.59';
        $carrier2_tx_oid = '.1.3.6.1.4.1.2509.9.3.2.1.1.11.60';
        $carrier2_rx_oid = '.1.3.6.1.4.1.2509.9.3.2.1.1.12.60';

        return array(
            new WirelessSensor('rate', $this->getDeviceId(), $carrier1_tx_oid, 'aviat-wtm-carrier1-tx-rate', 1, 'TX Capacity (Carrier1/1)', null, 1, 1000000),
            new WirelessSensor('rate', $this->getDeviceId(), $carrier1_rx_oid, 'aviat-wtm-carrier1-rx-rate', 1, 'RX Capacity (Carrier1/1)', null, 1, 1000000),
            new WirelessSensor('rate', $this->getDeviceId(), $carrier2_tx_oid, 'aviat-wtm-carrier2-tx-rate', 1, 'TX Capacity (Carrier1/2)', null, 1, 1000000),
            new WirelessSensor('rate', $this->getDeviceId(), $carrier1_rx_oid, 'aviat-wtm-carrier2-rx-rate', 1, 'RX Capacity (Carrier1/2)', null, 1, 1000000),
        );
    }

    /**
     * Discover wireless tx or rx RSL. This is in dbps. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */

    public function discoverWirelessRssi()
    {
        $carrier1_rsl_oid = '.1.3.6.1.4.1.2509.9.15.2.2.1.4.59'; 
        $carrier2_rsl_oid = '.1.3.6.1.4.1.2509.9.15.2.2.1.4.60';

        return array(
            new WirelessSensor('rssi', $this->getDeviceId(), $carrier1_rsl_oid, 'aviat-wtm-carrier1-rsl', 1, 'RSL (Carrier1/1)', null, 1, 10),
            new WirelessSensor('rssi', $this->getDeviceId(), $carrier2_rsl_oid, 'aviat-wtm-carrier2-rsl', 1, 'RSL (Carrier1/2)', null, 1, 10),
        );
    }

    /**
     * Discover wireless SNR (CINR). This is in dbm. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */

    public function discoverWirelessSnr()
    {
        $carrier1_snr_oid = '.1.3.6.1.4.1.2509.9.33.2.2.1.3.59';
        $carrier2_snr_oid = '.1.3.6.1.4.1.2509.9.33.2.2.1.3.60';

        return array(
            new WirelessSensor('snr', $this->getDeviceId(), $carrier1_snr_oid, 'aviat-wtm-carrier1-snr', 1, 'SNR (Carrier1/1)', null, 1, 10),
            new WirelessSensor('snr', $this->getDeviceId(), $carrier2_snr_oid, 'aviat-wtm-carrier2-snr', 1, 'SNR (Carrier1/2)', null, 1, 10),
        );
    }

    /**
     * Discover wireless TX power. This is in dbm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */

    public function discoverWirelessPower()
    {
        $carrier1_txpower_oid = '.1.3.6.1.4.1.2509.9.33.2.2.1.7.59';
        $carrier2_txpower_oid = '.1.3.6.1.4.1.2509.9.33.2.2.1.7.60';

        return array(
            new WirelessSensor('power', $this->getDeviceId(), $carrier1_txpower_oid, 'aviat-wtm-carrier1-txpower', 1, 'TX Power (Carrier1/1)', null, 1, 10),
            new WirelessSensor('power', $this->getDeviceId(), $carrier2_txpower_oid, 'aviat-wtm-carrier2-txpower', 1, 'TX Power (Carrier1/2)', null, 1, 10),
        );
    }
}
