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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 James Andrewartha
 * @author     James Andrewartha <trs80@ucc.asn.au>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\OS;

class Ewc extends OS implements
    WirelessApCountDiscovery,
    WirelessClientsDiscovery
{
    /**
     * Discover wireless AP count.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessApCount()
    {
        $oids = array(
            'HIPATH-WIRELESS-HWC-MIB::apCount.0',
            'HIPATH-WIRELESS-HWC-MIB::licenseLocalAP.0',
            'HIPATH-WIRELESS-HWC-MIB::licenseForeignAP.0'
        );
        $data = snmp_get_multi($this->getDevice(), $oids);
        $licCount = $data[0]['licenseLocalAP'] + $data[0]['licenseForeignAP'];
        return array(
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
            )
        );
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $sensors = array(
            new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4329.15.3.6.1.0',
                'ewc',
                0,
                'Connected Clients'
            )
        );

        $apstats = snmpwalk_cache_oid($this->getDevice(), 'apStatsMuCounts', array(), 'HIPATH-WIRELESS-HWC-MIB');
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

        $wlanstats = snmpwalk_cache_oid($this->getDevice(), 'wlanStatsAssociatedClients', array(), 'HIPATH-WIRELESS-HWC-MIB');
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
}
