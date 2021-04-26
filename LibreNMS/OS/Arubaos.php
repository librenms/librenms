<?php
/**
 * Arubaos.php
 *
 * HPE ArubaOS
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS;

class Arubaos extends OS implements
    OsDiscovery,
    WirelessApCountDiscovery,
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessUtilizationDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $aruba_info = snmp_get_multi($this->getDeviceArray(), [
            'wlsxSwitchRole.0',
            'wlsxSwitchMasterIp.0',
            'wlsxSwitchLicenseSerialNumber.0',
        ], '-OQUs', 'WLSX-SWITCH-MIB');

        $device->features = $aruba_info[0]['wlsxSwitchRole'] == 'master' ? 'Master Controller' : "Local Controller for {$aruba_info[0]['wlsxSwitchMasterIp']}";
        $device->serial = $aruba_info[0]['wlsxSwitchLicenseSerialNumber'];
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $oid = '.1.3.6.1.4.1.14823.2.2.1.1.3.2.0'; // WLSX-SWITCH-MIB::wlsxSwitchTotalNumStationsAssociated.0

        return [
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'arubaos', 1, 'Client Count'),
        ];
    }

    /**
     * Discover wireless AP counts. Type is ap-count.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessApCount()
    {
        $mib = 'WLSX-SWITCH-MIB';
        $data = $this->getCacheTable('wlsxSwitchTotalNumAccessPoints', $mib);
        $sensors = [];

        foreach ($data as $key => $value) {
            $oid = snmp_translate($mib . '::' . $key, 'ALL', 'arubaos', '-On');
            $value = intval($value);

            $low_warn_const = 1; // Default warning threshold = 1 down AP
            $low_limit_const = 10; // Default critical threshold = 10 down APs

            // Calculate default thresholds based on current AP count
            $low_warn = $value - $low_warn_const;
            $low_limit = $value - $low_limit_const;

            // For small current AP counts, set thresholds differently:
            // If AP count is less than twice the default critical threshold,
            // then set the critical threshold to roughly half the current AP count.
            if ($value < $low_limit_const * 2) {
                $low_limit = round($value / 2, 0, PHP_ROUND_HALF_DOWN);
            }
            // If AP count is less than the default warning hreshold,
            // then don't bother setting thresholds.
            if ($value <= $low_warn_const) {
                $low_warn = null;
                $low_limit = null;
            }

            // If AP count is less than twice the default warning threshold,
            // then set the critical threshold to zero.
            if ($value > 0 && $value <= $low_warn_const * 2) {
                $low_limit = 0;
            }

            $sensors[] = new WirelessSensor('ap-count', $this->getDeviceId(), $oid, 'arubaos', 1, 'AP Count', $value, 1, 1, 'sum', null, null, $low_limit, null, $low_warn);
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
        // instant
        return $this->discoverInstantRadio('frequency', 'aiRadioChannel');
    }

    /**
     * Discover wireless noise floor. This is in dBm/Hz. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor()
    {
        // instant
        return $this->discoverInstantRadio('noise-floor', 'aiRadioNoiseFloor');
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        // instant
        return $this->discoverInstantRadio('power', 'aiRadioTransmitPower', 'Radio %s: Tx Power');
    }

    protected function decodeChannel($channel)
    {
        return cast_number($channel) & 255; // mask off the channel width information
    }

    private function discoverInstantRadio($type, $oid, $desc = 'Radio %s')
    {
        $data = snmpwalk_cache_numerical_oid($this->getDeviceArray(), $oid, [], 'AI-AP-MIB');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $value = reset($entry);
            $oid = key($entry);

            if ($type == 'frequency') {
                $value = WirelessSensor::channelToFrequency($this->decodeChannel($value));
            }

            $sensors[] = new WirelessSensor(
                $type,
                $this->getDeviceId(),
                $oid,
                'arubaos-iap',
                $oid,
                sprintf($desc, $index),
                $value
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
        // instant
        return $this->discoverInstantRadio('utilization', 'aiRadioUtilization64');
    }

    /**
     * Poll wireless frequency as MHz
     * The returned array should be sensor_id => value pairs
     *
     * @param array $sensors Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessFrequency(array $sensors)
    {
        return $this->pollWirelessChannelAsFrequency($sensors, [$this, 'decodeChannel']);
    }
}
