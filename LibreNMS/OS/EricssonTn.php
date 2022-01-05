<?php
/**
 * EricssonTn.php
 *
 *
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
 * @copyright  2021 Maikel de Boer
 * @author     Maikel de Boer <maikel@loopodoopo.nl>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class EricssonTn extends OS implements
    WirelessMseDiscovery,
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessRateDiscovery
{
    public function discoverWirelessMSE()
    {
        $sensors = [];

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'xfModemEntry', [], 'XF-RADIOLINK-PTP-MODEM-MIB');
        $ifname = $this->getCacheTable('entPhysicalName', 'ENTITY-MIB');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'mse',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.193.81.3.4.2.1.1.1.15.' . $index,
                'ericsson-tn',
                $index,
                'MSE RAU: ' . $ifname[$index]['entPhysicalName'],
                null,
                1,
                10
            );
        }

        return $sensors;
    }

    public function discoverWirelessRate()
    {
        $sensors = [];

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'xfTermBitPipeCapacity', [], 'XF-RADIOLINK-PTP-TERMINAL-MIB');
        $carrier = $this->getCacheTable('xfTermSysName', 'XF-RADIOLINK-PTP-TERMINAL-MIB');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.193.81.3.4.1.1.14.1.7.' . $index,
                'ericsson-tn',
                $index,
                'Rate: ' . $carrier[$index]['xfTermSysName'],
                null,
                1000,
                1
            );
        }

        return $sensors;
    }

    public function discoverWirelessFrequency()
    {
        $sensors = [];

        $data_tx = snmpwalk_cache_oid($this->getDeviceArray(), 'xfRFBaseTxFrequency', [], 'XF-RADIOLINK-PTP-RADIO-MIB');
        $data_rx = snmpwalk_cache_oid($this->getDeviceArray(), 'xfRFBaseRxFrequency', [], 'XF-RADIOLINK-PTP-RADIO-MIB');
        $ifname = $this->getCacheTable('ifName', 'IF-MIB');
        foreach ($data_tx as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.193.81.3.4.3.1.2.1.1.' . $index,
                'ericsson-tn',
                $index . 'tx',
                'TX Frequency: ' . $ifname[$index]['ifName'],
                null,
                1,
                1000
            );
        }
        foreach ($data_rx as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.193.81.3.4.3.1.2.1.2.' . $index,
                'ericsson-tn',
                $index . 'rx',
                'RX Frequency: ' . $ifname[$index]['ifName'],
                null,
                1,
                1000
            );
        }

        return $sensors;
    }

    public function discoverWirelessPower()
    {
        $sensors = [];

        $data_tx = snmpwalk_cache_oid($this->getDeviceArray(), 'xfRFCurrentOutputPower', [], 'XF-RADIOLINK-PTP-RADIO-MIB');
        $data_rx = snmpwalk_cache_oid($this->getDeviceArray(), 'xfRFCurrentInputPower', [], 'XF-RADIOLINK-PTP-RADIO-MIB');
        $ifname = $this->getCacheTable('ifName', 'IF-MIB');
        foreach ($data_tx as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.193.81.3.4.3.1.3.1.1.' . $index,
                'ericsson-tn',
                $index . 'tx',
                'Output power: ' . $ifname[$index]['ifName'],
            );
        }
        foreach ($data_rx as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.193.81.3.4.3.1.3.1.10.' . $index,
                'ericsson-tn',
                $index . 'rx',
                'Input power: ' . $ifname[$index]['ifName'],
                null,
                1,
                10

            );
        }

        return $sensors;
    }
}
