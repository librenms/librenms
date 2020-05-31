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
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;

class AviatWtm extends OS implements
    OSDiscovery,
    WirelessFrequencyDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessPowerDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();

        $oids = ['entPhysicalModelName.2', 'entPhysicalSerialNum.2', 'entPhysicalSoftwareRev.2'];
        $data = snmp_get_multi($this->getDevice(), $oids, '-OQUs', 'ENTITY-MIB');

        $device->hardware = $data[2]['entPhysicalModelName'] ?? null;
        $device->serial = $data[2]['entPhysicalSerialNum'] ?? null;
        $device->version = $data[2]['entPhysicalSoftwareRev'] ?? null;
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */

    public function discoverWirelessFrequency()
    {
        $sensors = [];
        $name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $frequency = snmpwalk_cache_oid($this->getDevice(), 'aviatRfFreqTx', [], 'AVIAT-RF-MIB:');
        foreach ($frequency as $index => $data) {
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.5.2.1.1.1.$index",
                'aviat-wtm-carrier-tx-freq',
                $index,
                "TX Frequency ({$name[$index]})",
                $data['aviatRfFreqTx'],
                1,
                1000
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless tx or rx capacity. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */

    public function discoverWirelessRate()
    {
        $sensors = [];
        $name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');

        $tx = snmpwalk_cache_oid($this->getDevice(), 'aviatModemCurCapacityTx', [], 'AVIAT-MODEM-MIB');
        foreach ($tx as $index => $data) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.3.2.1.1.11.$index",
                'aviat-wtm-carrier-tx-rate',
                $index,
                "TX Capacity ({$name[$index]})",
                $data['aviatModemCurCapacityTx'],
                1000
            );
        }

        $rx = snmpwalk_cache_oid($this->getDevice(), 'aviatModemCurCapacityRx', [], 'AVIAT-MODEM-MIB');
        foreach ($rx as $index => $data) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.3.2.1.1.12.$index",
                'aviat-wtm-carrier-rx-rate',
                $index,
                "TX Capacity ({$name[$index]})",
                $data['aviatModemCurCapacityRx'],
                1000
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless tx or rx RSL. This is in dbm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $sensors = [];
        $name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $rsl = snmpwalk_cache_oid($this->getDevice(), 'aviatRxPerformRslReadingCurrent', [], 'AVIAT-RXPERFORMANCE-MIB');

        foreach ($rsl as $index => $data) {
            $sensors[] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.15.2.2.1.4.$index",
                'aviat-wtm-carrier-rsl',
                $index,
                "RSL ({$name[$index]})",
                $data['aviatRxPerformRslReadingCurrent'],
                1,
                10
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless SNR (CINR). This is in dbm. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessSnr()
    {
        $sensors = [];
        $name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $snr = snmpwalk_cache_oid($this->getDevice(), 'aviatRxPerformCinrReadingCurrent', [], 'AVIAT-RXPERFORMANCE-EX-MIB');

        foreach ($snr as $index => $data) {
            $sensors[] = new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.33.2.2.1.3.$index",
                'aviat-wtm-carrier-snr',
                $index,
                "SNR ({$name[$index]})",
                $data['aviatRxPerformCinrReadingCurrent'],
                1,
                10
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless TX power. This is in dbm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $sensors = [];
        $name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $power = snmpwalk_cache_oid($this->getDevice(), 'aviatRxPerformTxpowReadingCurrent', [], 'AVIAT-RXPERFORMANCE-EX-MIB');

        foreach ($power as $index => $data) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.33.2.2.1.7.$index",
                'aviat-wtm-carrier-txpower',
                $index,
                "TX Power ({$name[$index]})",
                $data['aviatRxPerformTxpowReadingCurrent'],
                1,
                10
            );
        }

        return $sensors;
    }
}
