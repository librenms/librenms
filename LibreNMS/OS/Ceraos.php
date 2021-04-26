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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessXpiDiscovery;
use LibreNMS\OS;

class Ceraos extends OS implements OSDiscovery, WirelessXpiDiscovery, WirelessFrequencyDiscovery, WirelessErrorsDiscovery, WirelessMseDiscovery, WirelessPowerDiscovery, WirelessRateDiscovery
{
    public function discoverOS(Device $device): void
    {
        $device->hardware = $this->fetchHardware();

        $sn_oid = Str::contains($device->hardware, 'IP10') ? 'genEquipUnitIDUSerialNumber.0' : 'genEquipInventorySerialNumber.127';
        $device->serial = snmp_get($this->getDeviceArray(), $sn_oid, '-Oqv', 'MWRM-UNIT-MIB');

        $data = snmp_get_multi($this->getDeviceArray(), ['genEquipMngSwIDUVersionsRunningVersion.1', 'genEquipUnitLatitude.0', 'genEquipUnitLongitude.0'], '-OQU', 'MWRM-RADIO-MIB');
        $device->version = $data[1]['MWRM-UNIT-MIB::genEquipMngSwIDUVersionsRunningVersion'] ?? null;

        // update location lat/lng
        if ($device->location && (! empty($multi_get_array[0]['MWRM-UNIT-MIB::genEquipUnitLatitude']) || ! empty($multi_get_array[0]['MWRM-UNIT-MIB::genEquipUnitLongitude']))) {
            $device->location->lat = $multi_get_array[0]['MWRM-UNIT-MIB::genEquipUnitLatitude'] ?? $device->location->lat;
            $device->location->lng = $multi_get_array[0]['MWRM-UNIT-MIB::genEquipUnitLongitude'] ?? $device->location->lng;
            $device->location->save();
        }

        $num_radios = 0;
        foreach (snmpwalk_group($this->getDeviceArray(), 'ifDescr', 'IF-MIB') as $interface) {
            if ($interface['ifDescr'] == 'Radio') {
                $num_radios++;
            }
        }

        $device->features = $num_radios . ' radios in unit';
    }

    public function discoverWirelessXpi()
    {
        $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');

        $sensors = [];
        $divisor = 100;

        $xpi = snmpwalk_group($this->getDeviceArray(), 'genEquipRadioStatusXPI', 'MWRM-RADIO-MIB');
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
        $tx = snmpwalk_group($this->getDeviceArray(), 'genEquipRfuCfgTxFreq', 'MWRM-RADIO-MIB');
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
        $rx = snmpwalk_group($this->getDeviceArray(), 'genEquipRfuCfgRxFreq', 'MWRM-RADIO-MIB');
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

        $tx = snmpwalk_group($this->getDeviceArray(), 'genEquipRadioMRMCCurrTxBitrate', 'MWRM-RADIO-MIB');
        foreach ($tx as $index => $data) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.7.4.1.1.7.' . $index,
                'ceraos-tx',
                $index,
                $ifNames[$index] . ' TX Bitrate',
                $data['genEquipRadioMRMCCurrTxBitrate'],
                1000
            );
        }

        $rx = snmpwalk_group($this->getDeviceArray(), 'genEquipRadioMRMCCurrRxBitrate', 'MWRM-RADIO-MIB');
        foreach ($rx as $index => $data) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.7.4.1.1.11.' . $index,
                'ceraos-rx',
                $index,
                $ifNames[$index] . ' RX Bitrate',
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

        $mse = snmpwalk_group($this->getDeviceArray(), 'genEquipRadioStatusDefectedBlocks', 'MWRM-RADIO-MIB');
        foreach ($mse as $index => $data) {
            $sensors[] = new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.7.1.1.3.' . $index,
                'ceraos',
                $index,
                $ifNames[$index] . ' Defected Blocks',
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

        $mse = snmpwalk_group($this->getDeviceArray(), 'genEquipRadioStatusMSE', 'MWRM-RADIO-MIB');
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

        $tx = snmpwalk_group($this->getDeviceArray(), 'genEquipRfuStatusTxLevel', 'MWRM-RADIO-MIB');
        foreach ($tx as $index => $data) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.5.1.1.3.' . $index,
                'ceraos-tx',
                $index,
                $ifNames[$index] . ' TX Level',
                $data['genEquipRfuStatusTxLevel']
            );
        }

        $rx = snmpwalk_group($this->getDeviceArray(), 'genEquipRfuStatusRxLevel', 'MWRM-RADIO-MIB');
        foreach ($rx as $index => $data) {
            $sensors[] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2281.10.5.1.1.2.' . $index,
                'ceraos-rx',
                $index,
                $ifNames[$index] . ' RX Level',
                $data['genEquipRfuStatusRxLevel']
            );
        }

        return $sensors;
    }

    private function fetchHardware()
    {
        $sysObjectID = $this->getDevice()->sysObjectID;

        if (Str::contains($sysObjectID, '.2281.1.10')) {
            return 'IP10 Family';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.1.1.2')) {
            return 'IP-20A 1RU';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.1.1.4')) {
            return 'IP-20 Evolution LH 1RU';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.1.1')) {
            return 'IP-20N 1RU';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.1.2.2')) {
            return 'IP-20A 2RU';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.1.2.4')) {
            return 'IP-20 Evolution 2RU';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.1.2')) {
            return 'IP-20N 2RU';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.1.3.1')) {
            return 'IP-20G';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.1.3.2')) {
            return 'IP-20GX';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.2.2.2')) {
            return 'IP-20S';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.2.2.3')) {
            return 'IP-20E (hardware release 1)';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.2.2.4')) {
            return 'IP-20E (hardware release 2)';
        } elseif (Str::contains($sysObjectID, '.2281.1.20.2.2')) {
            return 'IP-20C';
        }

        return snmp_get($this->getDeviceArray(), 'genEquipInventoryCardName', '-Oqv', 'MWRM-UNIT-NAME');
    }
}
