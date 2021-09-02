<?php
/**
 * Ewc.php
 *
 * Extreme Wireless Controller
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
 * @copyright  2017 James Andrewartha
 * @author     James Andrewartha <trs80@ucc.asn.au>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\OS;

class Ewc extends OS implements
    WirelessApCountDiscovery,
    WirelessClientsDiscovery,
    WirelessErrorsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessUtilizationDiscovery
{
    /**
     * Discover wireless AP count.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessApCount()
    {
        $oids = [
            'HIPATH-WIRELESS-HWC-MIB::apCount.0',
            'HIPATH-WIRELESS-HWC-MIB::licenseLocalAP.0',
            'HIPATH-WIRELESS-HWC-MIB::licenseForeignAP.0',
        ];
        $data = snmp_get_multi($this->getDeviceArray(), $oids);
        $licCount = $data[0]['licenseLocalAP'] + $data[0]['licenseForeignAP'];

        return [
            new WirelessSensor(
                'ap-count',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4329.15.3.5.2.1.0',
                'ewc',
                0,
                'Connected APs'
            ),
            new WirelessSensor(
                'ap-count',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4329.15.3.5.1.1.0',
                'ewc',
                1,
                'Configured APs',
                $data[0]['apCount'],
                1,
                1,
                'sum',
                null,
                $licCount
            ),
        ];
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $sensors = [
            new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4329.15.3.6.1.0',
                'ewc',
                0,
                'Connected Clients'
            ),
        ];

        $apstats = snmpwalk_cache_oid($this->getDeviceArray(), 'apStatsMuCounts', [], 'HIPATH-WIRELESS-HWC-MIB');
        $apnames = $this->getCacheByIndex('apName', 'HIPATH-WIRELESS-HWC-MIB');

        foreach ($apstats as $index => $entry) {
            $apStatsMuCounts = $entry['apStatsMuCounts'];
            $name = $apnames[$index];
            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                '1.3.6.1.4.1.4329.15.3.5.2.2.1.14.' . $index,
                'ewc',
                $index,
                "Clients ($name)",
                $apStatsMuCounts
            );
        }

        $wlanstats = snmpwalk_cache_oid($this->getDeviceArray(), 'wlanStatsAssociatedClients', [], 'HIPATH-WIRELESS-HWC-MIB');
        $wlannames = $this->getCacheByIndex('wlanName', 'HIPATH-WIRELESS-HWC-MIB');

        foreach ($wlanstats as $index => $entry) {
            $name = $wlannames[$index];
            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                '1.3.6.1.4.1.4329.15.3.3.4.5.1.2.' . $index,
                'ewc',
                $name,
                "SSID: $name"
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
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'apPerfRadioPktRetx', [], 'HIPATH-WIRELESS-HWC-MIB');
        $ap_interfaces = $this->getCacheByIndex('apName', 'HIPATH-WIRELESS-HWC-MIB');

        $sensors = [];
        foreach ($oids as $index => $entry) {
            $name = $ap_interfaces[explode('.', $index)[0]];
            $sensors[] = new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4329.15.3.5.2.5.1.18.' . $index,
                'ewc',
                $index . 'Retx',
                "Retransmits ($name radio " . explode('.', $index)[1] . ')'
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'apRadioStatusChannel', [], 'HIPATH-WIRELESS-HWC-MIB');
        $ap_interfaces = $this->getCacheByIndex('ifName', 'IF-MIB');

        $sensors = [];
        foreach ($oids as $index => $entry) {
            $name = $ap_interfaces[$index];
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4329.15.3.5.2.4.1.1.' . $index,
                'ewc',
                $index,
                "Frequency ($name)"
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless noise floor.  This is in dBm. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessNoiseFloor()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'dot11ExtRadioMaxNfCount', [], 'HIPATH-WIRELESS-DOT11-EXTNS-MIB');
        $ap_interfaces = $this->getCacheByIndex('ifName', 'IF-MIB');

        $sensors = [];
        foreach ($oids as $index => $entry) {
            $name = $ap_interfaces[$index];
            $noisefloor = $entry['dot11ExtRadioMaxNfCount'];
            $sensors[] = new WirelessSensor(
                'noise-floor',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4329.15.3.1.4.3.1.32.' . $index,
                'ewc',
                $index,
                "Noise floor ($name)",
                $noisefloor,
                1,
                1,
                'sum',
                null,
                -75
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRssi()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'apPerfRadioCurrentRSS', [], 'HIPATH-WIRELESS-HWC-MIB');
        $ap_interfaces = $this->getCacheByIndex('apName', 'HIPATH-WIRELESS-HWC-MIB');

        $sensors = [];
        foreach ($oids as $index => $entry) {
            $name = $ap_interfaces[explode('.', $index)[0]];
            $sensors[] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4329.15.3.5.2.5.1.9.' . $index,
                'ewc',
                $index,
                "RSS ($name radio " . explode('.', $index)[1] . ')'
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'apPerfRadioCurrentSNR', [], 'HIPATH-WIRELESS-HWC-MIB');
        $ap_interfaces = $this->getCacheByIndex('apName', 'HIPATH-WIRELESS-HWC-MIB');

        $sensors = [];
        foreach ($oids as $index => $entry) {
            $name = $ap_interfaces[explode('.', $index)[0]];
            $sensors[] = new WirelessSensor(
                'snr',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4329.15.3.5.2.5.1.13.' . $index,
                'ewc',
                $index,
                "SNR ($name radio " . explode('.', $index)[1] . ')'
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless utilization.  This is in %. Type is utilization.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessUtilization()
    {
        $oids = snmpwalk_cache_oid($this->getDeviceArray(), 'apPerfRadioCurrentChannelUtilization', [], 'HIPATH-WIRELESS-HWC-MIB');
        $ap_interfaces = $this->getCacheByIndex('apName', 'HIPATH-WIRELESS-HWC-MIB');

        $sensors = [];
        foreach ($oids as $index => $entry) {
            $name = $ap_interfaces[explode('.', $index)[0]];
            $sensors[] = new WirelessSensor(
                'utilization',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4329.15.3.5.2.5.1.5.' . $index,
                'ewc',
                $index,
                "Utilization ($name radio " . explode('.', $index)[1] . ')'
            );
        }

        return $sensors;
    }
}
