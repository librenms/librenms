<?php
/**
 * Teldat.php
 *
 * Teldat Devices
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

namespace LibreNMS\OS;

use Illuminate\Support\Str;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCellDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSinrDiscovery;
use LibreNMS\OS;

class Teldat extends OS implements
    WirelessCellDiscovery,
    WirelessClientsDiscovery,
    WirelessRssiDiscovery,
    WirelessRsrqDiscovery,
    WirelessRsrpDiscovery,
    WirelessSinrDiscovery
{
    /**
     * Return Cellular Short Interface Name.
     *
     * @param  string  $ifName
     * @param  int  $ifIndex
     * @return string with Short Interface Name
     */
    public function shortIfName($ifName, $ifIndex)
    {
        $device = ($ifName == '' ? strval($ifIndex) : preg_replace('/cellular/', 'Ce', $ifName));

        return $device;
    }

    /**
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $sensors = [];

        // telProdNpMonInterfWlanBSSCurrent :: Count of current associated stations
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'telProdNpMonInterfWlanBSSCurrent', [], 'TELDAT-MON-INTERF-WLAN-MIB');

        if (! empty($data)) {
            $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');
            $ifOperStatuses = $this->getCacheByIndex('ifOperStatus', 'IF-MIB');

            foreach ($data as $index => $entry) {
                if (Str::startsWith($ifNames[$index], 'wlan') && $ifOperStatuses[$index] == 'up') {
                    $sensors[] = new WirelessSensor(
                        'clients',
                        $this->getDeviceId(),
                        ".1.3.6.1.4.1.2007.4.1.2.2.2.24.2.1.23.$index",
                        'teldat',
                        $index,
                        $ifNames[$index],
                        $entry['telProdNpMonInterfWlanBSSCurrent'],
                        1,
                        1,
                        'sum',
                        null,
                        null,
                        40,
                        null,
                        30,
                        $index,
                        null
                    );
                }
            }
        }

        return $sensors;
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

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileSignalQuality', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        if (! empty($data)) {
            $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');
            $ifOperStatuses = $this->getCacheByIndex('ifOperStatus', 'IF-MIB');

            foreach ($data as $index => $entry) {
                if (Str::startsWith($ifNames[$index], 'cellular') && $ifOperStatuses[$index] == 'up') {
                    $sensors[] = new WirelessSensor(
                        'rssi',
                        $this->getDeviceId(),
                        '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.10.' . $index,
                        'teldat',
                        $index,
                        'RSSI: ' . $this->shortIfName($ifNames[$index], $index),
                        $entry['teldatCellularStateMobileSignalQuality']
                    );
                }
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

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileRxSINR', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        if (! empty($data)) {
            $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');
            $ifOperStatuses = $this->getCacheByIndex('ifOperStatus', 'IF-MIB');

            foreach ($data as $index => $entry) {
                if (Str::startsWith($ifNames[$index], 'cellular') && $ifOperStatuses[$index] == 'up') {
                    $sensors[] = new WirelessSensor(
                        'sinr',
                        $this->getDeviceId(),
                        '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.24.' . $index,
                        'teldat',
                        $index,
                        'SINR: ' . $this->shortIfName($ifNames[$index], $index),
                        $entry['teldatCellularStateMobileRxSINR']
                    );
                }
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

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileRxRSRQ', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        if (! empty($data)) {
            $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');
            $ifOperStatuses = $this->getCacheByIndex('ifOperStatus', 'IF-MIB');

            foreach ($data as $index => $entry) {
                if (Str::startsWith($ifNames[$index], 'cellular') && $ifOperStatuses[$index] == 'up') {
                    $sensors[] = new WirelessSensor(
                        'rsrq',
                        $this->getDeviceId(),
                        '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.23.' . $index,
                        'teldat',
                        $index,
                        'RSRQ: ' . $this->shortIfName($ifNames[$index], $index),
                        $entry['teldatCellularStateMobileRxRSRQ']
                    );
                }
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

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileRxRSRP', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        if (! empty($data)) {
            $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');
            $ifOperStatuses = $this->getCacheByIndex('ifOperStatus', 'IF-MIB');

            foreach ($data as $index => $entry) {
                if (Str::startsWith($ifNames[$index], 'cellular') && $ifOperStatuses[$index] == 'up') {
                    $sensors[] = new WirelessSensor(
                        'rsrp',
                        $this->getDeviceId(),
                        '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.22.' . $index,
                        'teldat',
                        $index,
                        'RSRP: ' . $this->shortIfName($ifNames[$index], $index),
                        $entry['teldatCellularStateMobileRxRSRP']
                    );
                }
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
        $ifNames = [];
        $ifOperStatuses = [];

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileCellId', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        if (! empty($data)) {
            $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');
            $ifOperStatuses = $this->getCacheByIndex('ifOperStatus', 'IF-MIB');

            foreach ($data as $index => $entry) {
                if (Str::startsWith($ifNames[$index], 'cellular') && $ifOperStatuses[$index] == 'up') {
                    $sensors[] = new WirelessSensor(
                        'cell',
                        $this->getDeviceId(),
                        '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.5.' . $index,
                        'teldat',
                        $index,
                        'CellID: ' . $this->shortIfName($ifNames[$index], $index),
                        $entry['teldatCellularStateMobileCellId']
                    );
                }
            }
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileLTECellId', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        if (! empty($data)) {
            if (empty($ifNames)) {
                $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');
            }
            if (empty($ifOperStatuses)) {
                $ifOperStatuses = $this->getCacheByIndex('ifOperStatus', 'IF-MIB');
            }

            foreach ($data as $index => $entry) {
                if (Str::startsWith($ifNames[$index], 'cellular') && $ifOperStatuses[$index] == 'up') {
                    $sensors[] = new WirelessSensor(
                        'cell',
                        $this->getDeviceId(),
                        '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.25.' . $index,
                        'teldat',
                        (10 * count($sensors)) + $index,
                        'LteCellID: ' . $this->shortIfName($ifNames[$index], $index),
                        $entry['teldatCellularStateMobileLTECellId']
                    );
                }
            }
        }

        return $sensors;
    }
}
