<?php
/**
 * TeldatCellular.php
 *
 * -Description-
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
 * @copyright  2021 Antonio Pedro Santos
 * @author     Antonio Pedro Santos <cupidazul@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use LibreNMS\Device\WirelessSensor;

trait TeldatCellular
{
    private $cwceLteProfileApn = [];

    public function profileApn($index)
    {
        if (empty($this->deviceInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return '';
        }

        $apn = '';
        if (empty($this->cwceLteProfileApn)) {
            $this->cwceLteProfileApn = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularProfDialAPN1', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');
        }

        $device = $this->deviceInfo;
        if (isset($device[$index])) {
            $device = ($device[$index]['ifName'] == '' ? strval($index) : preg_replace('/cellular/', 'Ce', $device[$index]['ifName']));
            $apn = $this->cwceLteProfileApn[$index . '.1']['teldatCellularProfDialAPN1'];
        }

        if ($apn == '') {
            return $device;
        }

        return $device . ' ' . $apn;
    }

    /**
     * teldatCellularStateMobileSignalQuality = .1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.10 = "Cellular mobile reception signal quality (+CSQ)."
     *
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $sensors = [];

        if (empty($this->deviceInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return $sensors;
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileSignalQuality', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        foreach ($data as $index => $entry) {
            if (isset($this->deviceInfo[$index]) and $this->deviceInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'rssi',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.10.' . $index,
                    'teldat',
                    $index,
                    'RSSI: ' . $this->profileApn($index),
                    $entry['teldatCellularStateMobileSignalQuality.1']
                );
            }
        }

        return $sensors;
    }

    /**
     * teldatCellularStateMobileRxSINR = .1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.24 = "Cellular mobile signal versus noise ratio (SINR)."
     *
     * Discover wireless SINR (Signal-to-Interference-plus-Noise Ratio). This is in dB. Type is sinr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessSinr()
    {
        $sensors = [];

        if (empty($this->deviceInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return $sensors;
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileRxSINR', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        foreach ($data as $index => $entry) {
            if (isset($this->deviceInfo[$index]) and $this->deviceInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'sinr',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.24.' . $index,
                    'teldat',
                    $index,
                    'SINR: ' . $this->profileApn($index),
                    $entry['teldatCellularStateMobileRxSINR.1']
                );
            }
        }

        return $sensors;
    }

    /**
     * teldatCellularStateMobileRxRSRQ = .1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.23 = "Cellular mobile reference signal received quality (RSRQ)."
     *
     * Discover wireless RSRQ (Reference Signal Received Quality). This is in dB. Type is rsrq.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRsrq()
    {
        $sensors = [];

        if (empty($this->deviceInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return $sensors;
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileRxRSRQ', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        foreach ($data as $index => $entry) {
            if (isset($this->deviceInfo[$index]) and $this->deviceInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'rsrq',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.23.' . $index,
                    'teldat',
                    $index,
                    'RSRQ: ' . $this->profileApn($index),
                    $entry['teldatCellularStateMobileRxRSRQ.1']
                );
            }
        }

        return $sensors;
    }

    /**
     * teldatCellularStateMobileRxRSRP = .1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.22 = "Cellular mobile reference symbol received power (RSRP)."
     *
     * Discover wireless RSRP (Reference Signals Received Power). This is in dBm. Type is rsrp.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRsrp()
    {
        $sensors = [];

        if (empty($this->deviceInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return $sensors;
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileRxRSRP', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        foreach ($data as $index => $entry) {
            if (isset($this->deviceInfo[$index]) and $this->deviceInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'rsrp',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.22.' . $index,
                    'teldat',
                    $index,
                    'RSRP: ' . $this->profileApn($index),
                    $entry['teldatCellularStateMobileRxRSRP.1']
                );
            }
        }

        return $sensors;
    }

    /**
     * teldatCellularStateMobileCellId    = .1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.5  = "Cellular mobile used cell id (+CGREG)."
     * teldatCellularStateMobileLTECellId = .1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.25 = "Cellular mobile used LTE cell id."
     *
     * Discover wireless Cellular Cell Id. This is in cell number. Type is cellid.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessCell()
    {
        $sensors = [];

        if (empty($this->deviceInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return $sensors;
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileCellId', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        foreach ($data as $index => $entry) {
            if (isset($this->deviceInfo[$index]) and $this->deviceInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'cell',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.5.' . $index,
                    'teldat',
                    $index,
                    'CellID: ' . $this->profileApn($index),
                    $entry['teldatCellularStateMobileCellId.1']
                );
            }
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileLTECellId', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');
        if (empty($this->deviceInfo)) {
            $this->getdevicePortsInfo();
        }
        foreach ($data as $index => $entry) {
            if (isset($this->deviceInfo[$index]) and $this->deviceInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'cell',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.25.' . $index,
                    'teldat',
                    (10 * count($sensors)) + $index,
                    'LteCellID: ' . $this->profileApn($index),
                    $entry['teldatCellularStateMobileLTECellId.1']
                );
            }
        }

        return $sensors;
    }
}
