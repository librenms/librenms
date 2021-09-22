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

use App\Models\Port;
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
     * deviceIfInfo will cache an ifIndex'ed array with 'ifName' and 'ifOperStatus' for internal use in this class
     *
     * @var array
     */
    private $deviceIfInfo = [];
    
    /** @var bool */
    private $hasWLANif = false;
    
    /** @var bool */
    private $hasGSMif = false;

    /**
     * getdevicePortsInfo will create/update $deviceIfInfo array
     *
     * @return void
     */
    public function getdevicePortsInfo()
    {

        $devicePortsInfo = Port::whereIn('device_id', [$this->getDeviceId()])
            ->select('ifIndex', 'ifName', 'ifOperStatus')
            ->get();

        if (!isset($this->deviceIfInfo) or (count($this->deviceIfInfo) == 0)) {

            $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');
            $ifOperStatuss = $this->getCacheByIndex('ifOperStatus', 'IF-MIB');

            foreach ($ifNames as $entIndex => $entValue) {
                if (!isset($this->deviceIfInfo[$entIndex]))$this->deviceIfInfo[$entIndex] = [];
                $this->deviceIfInfo[$entIndex]['ifName'] = $entValue;
            }

            foreach ($ifOperStatuss as $entIndex => $entValue) {
                if (!isset($this->deviceIfInfo[$entIndex]))$this->deviceIfInfo[$entIndex] = [];
                $this->deviceIfInfo[$entIndex]['ifOperStatus'] = $entValue;
            
                $device = $this->deviceIfInfo[$entIndex];

                // Check if any wlan interface is up
                if (Str::startsWith($device['ifName'], 'wlan') and $device['ifOperStatus'] == 'up') {
                    $this->hasWLANif = true;
                }

                // Check if any cellular interface is up
                if (Str::startsWith($device['ifName'], 'cellular') and $device['ifOperStatus'] == 'up') {
                    $this->hasGSMif = true;
                }
            }

        } else {

            foreach ($devicePortsInfo as $entIndex => $entValue) {
                $ifIndex = $entValue['ifIndex'];
                $this->deviceIfInfo[$ifIndex]['ifName'] = $entValue['ifName'];
                $this->deviceIfInfo[$ifIndex]['ifOperStatus'] = $entValue['ifOperStatus'];
            }    

            foreach ($this->deviceIfInfo as $entIndex => $entValue) {
                // Check if any wlan interface is up
                if (Str::startsWith($entValue['ifName'], 'wlan') and $entValue['ifOperStatus'] == 'up') {
                    $this->hasWLANif = true;
                }

                // Check if any cellular interface is up
                if (Str::startsWith($entValue['ifName'], 'cellular') and $entValue['ifOperStatus'] == 'up') {
                    $this->hasGSMif = true;
                }
            }

        }

        d_echo('@getdevicePortsInfo: deviceIfInfo:'. print_r($this->deviceIfInfo, true));

    }

    /**
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $sensors = [];

        if (empty($this->deviceIfInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasWLANif) {
            return $sensors;
        }

        $device = $this->getDeviceArray();
        // telProdNpMonInterfWlanBSSCurrent :: Count of current associated stations
        $data = snmpwalk_cache_oid($device, 'telProdNpMonInterfWlanBSSCurrent', [], 'TELDAT-MON-INTERF-WLAN-MIB');

        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'clients',
                $device['device_id'],
                ".1.3.6.1.4.1.2007.4.1.2.2.2.24.2.1.23.$index",
                'teldat',
                $index,
                $this->deviceIfInfo[$index]['ifName'],
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

        return $sensors;
    }

    /**
     * Return Cellular Short Interface Name.
     *
     * @param  int  $index
     * 
     * @return string with Short Interface Name
     */
    public function shortIfName($index)
    {
        if (empty($this->deviceIfInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return '';
        }

        $device = '';
        if (isset($this->deviceIfInfo[$index]['ifName'])) {
            $device = ($this->deviceIfInfo[$index]['ifName'] == '' ? strval($index) : preg_replace('/cellular/', 'Ce', $this->deviceIfInfo[$index]['ifName']));
        }

        return $device;
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

        if (empty($this->deviceIfInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return $sensors;
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileSignalQuality', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        foreach ($data as $index => $entry) {
            if (isset($this->deviceIfInfo[$index]['ifOperStatus']) and $this->deviceIfInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'rssi',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.10.' . $index,
                    'teldat',
                    $index,
                    'RSSI: ' . $this->shortIfName($index),
                    $entry['teldatCellularStateMobileSignalQuality']
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

        if (empty($this->deviceIfInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return $sensors;
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileRxSINR', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        foreach ($data as $index => $entry) {
            if (isset($this->deviceIfInfo[$index]['ifOperStatus']) and $this->deviceIfInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'sinr',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.24.' . $index,
                    'teldat',
                    $index,
                    'SINR: ' . $this->shortIfName($index),
                    $entry['teldatCellularStateMobileRxSINR']
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

        if (empty($this->deviceIfInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return $sensors;
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileRxRSRQ', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        foreach ($data as $index => $entry) {
            if (isset($this->deviceIfInfo[$index]['ifOperStatus']) and $this->deviceIfInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'rsrq',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.23.' . $index,
                    'teldat',
                    $index,
                    'RSRQ: ' . $this->shortIfName($index),
                    $entry['teldatCellularStateMobileRxRSRQ']
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

        if (empty($this->deviceIfInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return $sensors;
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileRxRSRP', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        foreach ($data as $index => $entry) {
            if (isset($this->deviceIfInfo[$index]['ifOperStatus']) and $this->deviceIfInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'rsrp',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.22.' . $index,
                    'teldat',
                    $index,
                    'RSRP: ' . $this->shortIfName($index),
                    $entry['teldatCellularStateMobileRxRSRP']
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

        if (empty($this->deviceIfInfo)) {
            $this->getdevicePortsInfo();
        }
        if (! $this->hasGSMif) {
            return $sensors;
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileCellId', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');

        foreach ($data as $index => $entry) {
            if (isset($this->deviceIfInfo[$index]['ifOperStatus']) and $this->deviceIfInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'cell',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.5.' . $index,
                    'teldat',
                    $index,
                    'CellID: ' . $this->shortIfName($index),
                    $entry['teldatCellularStateMobileCellId']
                );
            }
        }

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'teldatCellularStateMobileLTECellId', [], 'TELDAT-MON-INTERF-CELLULAR-MIB');
        if (empty($this->deviceIfInfo)) {
            $this->getdevicePortsInfo();
        }
        foreach ($data as $index => $entry) {
            if (isset($this->deviceIfInfo[$index]['ifOperStatus']) and $this->deviceIfInfo[$index]['ifOperStatus'] == 'up') {
                $sensors[] = new WirelessSensor(
                    'cell',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2007.4.1.2.2.2.18.3.2.1.25.' . $index,
                    'teldat',
                    (10 * count($sensors)) + $index,
                    'LteCellID: ' . $this->shortIfName($index),
                    $entry['teldatCellularStateMobileLTECellId']
                );
            }
        }

        return $sensors;
    }
}
