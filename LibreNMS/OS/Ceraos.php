<?php
/**
 * Ceraos.php
 *
 * Ceragon CeraOS
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessXpiDiscovery;
use LibreNMS\OS;

class Ceraos extends OS implements WirelessXpiDiscovery, WirelessFrequencyDiscovery, WirelessErrorsDiscovery, WirelessMseDiscovery, WirelessPowerDiscovery, WirelessRateDiscovery
{
    public function discoverWirelessXpi()
    {
        $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');

        $sensors = [];
        $divisor = 100;

        $xpi = snmpwalk_group($this->getDevice(), 'genEquipRadioStatusXPI', 'MWRM-RADIO-MIB');
        foreach ($xpi as $index => $data) {
            $sensors[] = new WirelessSensor(
                'xpi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.7.1.1.5.' . $index,
                'ceraos',
                $index,
                $ifNames[$index],
                $data['genEquipRadioStatusXPI'] / $divisor,
                1,
                $divisor
            );
        }

        return $sensors;
    }

    public function discoverWirelessFrequency()
    {
        $sensors = [];
        // MWRM-RADIO-MIB::genEquipRfuCfgTxFreq
        $tx = snmpwalk_group($this->getDevice(), 'genEquipRfuCfgTxFreq', 'MWRM-RADIO-MIB');
        $TxRadio = 0;
        foreach ($tx as $index => $data) {
            $TxRadio++;
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.5.2.1.3.' . $index,
                'Ceraos-tx-radio ' . $TxRadio,
                1,
                'Tx Frequency Radio ' . $TxRadio,
                null,
                1,
                1000
            );
        }
        // MWRM-RADIO-MIB::genEquipRfuCfgRxFreq
        $rx = snmpwalk_group($this->getDevice(), 'genEquipRfuCfgRxFreq', 'MWRM-RADIO-MIB');
        $RxRadio = 0;
        foreach ($rx as $index => $data) {
            $RxRadio++;
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.5.2.1.4.' . $index,
                'Ceraos-rx-radio ' . $RxRadio,
                1,
                'Rx Frequency Radio ' . $RxRadio,
                null,
                1,
                1000
            );
        }
        return $sensors;
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');

        $sensors = [];

        $tx = snmpwalk_group($this->getDevice(), 'genEquipRadioMRMCCurrTxBitrate', 'MWRM-RADIO-MIB');
        foreach ($tx as $index => $data) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.7.4.1.1.7.' . $index,
                'ceraos-tx',
                $index,
                $ifNames[$index] . " TX Bitrate",
                $data['genEquipRadioMRMCCurrTxBitrate'],
                1000
            );
        }

        $rx = snmpwalk_group($this->getDevice(), 'genEquipRadioMRMCCurrRxBitrate', 'MWRM-RADIO-MIB');
        foreach ($rx as $index => $data) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.7.4.1.1.11.' . $index,
                'ceraos-rx',
                $index,
                $ifNames[$index] . " RX Bitrate",
                $data['genEquipRadioMRMCCurrRxBitrate'],
                1000
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless bit errors.  This is in total bits. Type is errors.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessErrors()
    {
        $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');

        $sensors = [];

        $mse = snmpwalk_group($this->getDevice(), 'genEquipRadioStatusDefectedBlocks', 'MWRM-RADIO-MIB');
        foreach ($mse as $index => $data) {
            $sensors[] = new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.7.1.1.3.' . $index,
                'ceraos',
                $index,
                $ifNames[$index] . " Defected Blocks",
                $data['genEquipRadioStatusDefectedBlocks']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless MSE. Mean square error value in dB. Type is mse.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessMse()
    {
        $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');

        $sensors = [];
        $divisor = 100;

        $mse = snmpwalk_group($this->getDevice(), 'genEquipRadioStatusMSE', 'MWRM-RADIO-MIB');
        foreach ($mse as $index => $data) {
            $sensors[] = new WirelessSensor(
                'mse',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.7.1.1.2.' . $index,
                'ceraos',
                $index,
                $ifNames[$index],
                $data['genEquipRadioStatusMSE'] / $divisor,
                1,
                $divisor
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');

        $sensors = [];

        $tx = snmpwalk_group($this->getDevice(), 'genEquipRfuStatusTxLevel', 'MWRM-RADIO-MIB');
        foreach ($tx as $index => $data) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.5.1.1.3.' . $index,
                'ceraos-tx',
                $index,
                $ifNames[$index] . " TX Level",
                $data['genEquipRfuStatusTxLevel']
            );
        }

        $rx = snmpwalk_group($this->getDevice(), 'genEquipRfuStatusRxLevel', 'MWRM-RADIO-MIB');
        foreach ($rx as $index => $data) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.5.1.1.2.' . $index,
                'ceraos-rx',
                $index,
                $ifNames[$index] . " RX Level",
                $data['genEquipRfuStatusRxLevel']
            );
        }

        return $sensors;
    }
}
