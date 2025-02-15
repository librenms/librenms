<?php

/**
 * WisBridge.php
 *
 * Wis WisBridge
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
 * @copyright  2024 eric
 * @author     eric <eric@lanbowan.net>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Location;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCapacityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCcqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;

class WisBridge extends OS implements
    OSDiscovery,
    WirelessCapacityDiscovery,
    WirelessCcqDiscovery,
    WirelessClientsDiscovery,
    WirelessDistanceDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessQualityDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery
{
    public function discoverOS(Device $device): void
    {
        $response = \SnmpQuery::next([
            'IEEE802dot11-MIB::dot11manufacturerProductName',
            'IEEE802dot11-MIB::dot11manufacturerProductVersion',
        ]);

        $device->hardware = $response->value('IEEE802dot11-MIB::dot11manufacturerProductName') ?: null;

        $version = $response->value('IEEE802dot11-MIB::dot11manufacturerProductVersion');
        preg_match('/\.v(.*)$/', $version, $matches);
        $device->version = $matches[1] ?? null;
    }

    public function fetchLocation(): Location
    {
        $location = parent::fetchLocation();

        // fix having an extra - in the middle after the decimal point
        $regex = '/(-?\d+)\.-?(\d+)/';
        $location->lng = (float) preg_replace($regex, '$1.$2', $location->getAttributes()['lng'] ?? '');
        $location->lat = (float) preg_replace($regex, '$1.$2', $location->getAttributes()['lat'] ?? '');

        return $location;
    }

    /**
     * Discover wireless frequency.  This is in Hz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $oid = '.1.3.6.1.4.1.62821.1.4.1.1.4.1'; //WIS-BRIDGE-MIB::wisRadioFreq.1

        return [
            new WirelessSensor('frequency', $this->getDeviceId(), $oid, 'wis-bridge', 1, 'Radio Frequency'),
        ];
    }

    /**
     * Discover wireless capacity.  This is a percent. Type is capacity.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessCapacity()
    {
        $oid = '.1.3.6.1.4.1.62821.1.4.6.1.4.1';

        return [
            new WirelessSensor('capacity', $this->getDeviceId(), $oid, 'wis-bridge', 1, 'wisMax Capacity'),
        ];
    }

    /**
     * Discover wireless client connection quality.  This is a percent. Type is ccq.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessCcq()
    {
        $oid = '.1.3.6.1.4.1.62821.1.4.3.1.7.1'; //WIS-BRIDGE-MIB::wisWlStatCcq.1
        $oid = '';

        return [
            new WirelessSensor('ccq', $this->getDeviceId(), $oid, 'wis-bridge', 1, 'CCQ'),
        ];
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $oid = '.1.3.6.1.4.1.62821.1.4.3.1.15.1'; //WIS-BRIDGE-MIB::wisWlStatStaCount.1

        return [
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'wis-bridge', 1, 'Clients'),
        ];
    }

    /**
     * Discover wireless distance.  This is in kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessDistance()
    {
        $oid = '.1.3.6.1.4.1.62821.1.4.1.1.7.1'; //WIS-BRIDGE-MIB::wisRadioDistance.1
        $oid = '';

        return [
            new WirelessSensor('distance', $this->getDeviceId(), $oid, 'wis-bridge', 1, 'Distance', null, 1, 1000),
        ];
    }

    /**
     * Discover wireless noise floor. This is in dBm/Hz. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor()
    {
        $oid = '.1.3.6.1.4.1.62821.1.4.3.1.8.1'; //WIS-BRIDGE-MIB::wisWlStatNoiseFloor.1

        return [
            new WirelessSensor('noise-floor', $this->getDeviceId(), $oid, 'wis-bridge', 1, 'Noise Floor'),
        ];
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $tx_oid = '.1.3.6.1.4.1.62821.1.4.1.1.6.1'; //WIS-BRIDGE-MIB::wisRadioTxPower.1
        $rx_oid = '.1.3.6.1.4.1.62821.1.4.3.1.5.1'; //WIS-BRIDGE-MIB::wisWlStatSignal.1

        return [
            new WirelessSensor('power', $this->getDeviceId(), $tx_oid, 'wis-bridge-tx', 1, 'Tx Power'),
            new WirelessSensor('power', $this->getDeviceId(), $rx_oid, 'wis-bridge-rx', 1, 'Signal Level'),
        ];
    }

    /**
     * Discover wireless quality.  This is a percent. Type is quality.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessQuality()
    {
        $oidAirMax = '.1.3.6.1.4.1.62821.1.4.6.1.3.1';
        $oidAirMax = '';

        return [
            new WirelessSensor('quality', $this->getDeviceId(), $oidAirMax, 'wis-bridge', 1, 'airMAX Quality'),
        ];
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $tx_oid = '.1.3.6.1.4.1.62821.1.4.3.1.9.1'; //WIS-BRIDGE-MIB::wisWlStatTxRate.1
        $rx_oid = '.1.3.6.1.4.1.62821.1.4.3.1.10.1'; //WIS-BRIDGE-MIB::wisWlStatRxRate.1

        return [
            new WirelessSensor('rate', $this->getDeviceId(), $tx_oid, 'wis-bridge-tx', 1, 'Tx Rate'),
            new WirelessSensor('rate', $this->getDeviceId(), $rx_oid, 'wis-bridge-rx', 1, 'Rx Rate'),
        ];
    }

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.62821.1.4.3.1.6.1'; //WIS-BRIDGE-MIB::wisWlStatRssi.1
        $sensors = [
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'wis-bridge', 0, 'Overall RSSI'),
        ];

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'wisRadioRssi', [], 'WIS-BRIDGE-MIB');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.62821.1.4.2.1.2.' . $index,
                'wis-bridge',
                $index,
                'RSSI: Chain ' . str_replace('1.', '', $index),
                $entry['wisRadioRssi']
            );
        }

        return $sensors;
    }
}
