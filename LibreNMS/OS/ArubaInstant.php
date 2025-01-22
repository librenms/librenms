<?php
/**
 * ArubaInstant.php
 *
 * HPE Aruba Instant
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
 * @copyright  2019 Timothy Willey
 * @author     Timothy Willey <developer@timothywilley.net>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\EntPhysical;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessApCountPolling;
use LibreNMS\Interfaces\Polling\Sensors\WirelessClientsPolling;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Number;
use SnmpQuery;

class ArubaInstant extends OS implements
    OSDiscovery,
    ProcessorDiscovery,
    WirelessApCountDiscovery,
    WirelessApCountPolling,
    WirelessClientsDiscovery,
    WirelessClientsPolling,
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessUtilizationDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $device->serial = SnmpQuery::next('AI-AP-MIB::aiAPSerialNum')->value();
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors(): array
    {
        return SnmpQuery::cache()->walk('AI-AP-MIB::aiAccessPointTable')
            ->mapTable(function ($data, $aiAPMACAddress) {
                $mac = Mac::parse($aiAPMACAddress);
                $oid = '.1.3.6.1.4.1.14823.2.3.3.1.2.1.1.7.' . $mac->oid();
                $description = $data['AI-AP-MIB::aiAPSerialNum'];

                return Processor::discover('aruba-instant', $this->getDeviceId(), $oid, $mac->hex(), $description, 1, $data['AI-AP-MIB::aiAPCPUUtilization']);
            })->all();
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients(): array
    {
        $sensors = [];
        if (version_compare($this->getDevice()->version, '8.4.0.0', '>=')) {
            // version is at least 8.4.0.0
            $ap_data = SnmpQuery::cache()->walk('AI-AP-MIB::aiAccessPointTable')->table(1); // aiAPMACAddress
            $ssid_data = SnmpQuery::walk('AI-AP-MIB::aiWlanSSIDTable')->table(1); // aiSSIDIndex
            $client_num = SnmpQuery::walk('AI-AP-MIB::aiRadioClientNum')->table(2); // aiRadioAPMACAddress, aiRadioIndex

            $oids = [];
            $total_clients = 0;

            // Clients Per SSID
            foreach ($ssid_data as $index => $entry) {
                $oid = '.1.3.6.1.4.1.14823.2.3.3.1.1.7.1.4.' . $index;
                $description = sprintf('SSID %s Clients', $entry['AI-AP-MIB::aiSSID']);
                $oids[] = $oid;
                $total_clients += $entry['AI-AP-MIB::aiSSIDClientNum'];
                $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oid, 'aruba-instant', $index, $description, $entry['AI-AP-MIB::aiSSIDClientNum']);
            }

            // Total Clients across all SSIDs
            $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oids, 'aruba-instant', 'total-clients', 'Total Clients', $total_clients);

            // Clients Per Radio
            foreach ($client_num as $index => $entry) {
                foreach ($entry as $radio => $value) {
                    $mac = Mac::parse($index);
                    $oid = '.1.3.6.1.4.1.14823.2.3.3.1.2.2.1.21.' . $mac->oid() . '.' . $radio;
                    $description = sprintf('%s Radio %s', $ap_data[$index]['AI-AP-MIB::aiAPSerialNum'], $radio);
                    $sensor_index = sprintf('%s.%s', $mac->hex(), $radio);
                    $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oid, 'aruba-instant', $sensor_index, $description, $value['AI-AP-MIB::aiRadioClientNum']);
                }
            }
        } else {
            // version is lower than 8.4.0.0
            // fetch the MAC addresses of currently connected clients, then count them to get an overall total
            $client_data = SnmpQuery::walk('AI-AP-MIB::aiClientMACAddress')->table(1);  // aiClientMACAddress

            $total_clients = count($client_data);
            $oid = '.1.3.6.1.4.1.14823.2.3.3.1.2.4.1.1';

            $sensors[] = new WirelessSensor('clients', $this->getDeviceId(), $oid, 'aruba-instant', 'total-clients', 'Total Clients', $total_clients);
        }

        return $sensors;
    }

    /**
     * Discover wireless AP counts. Type is ap-count.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessApCount(): array
    {
        $sensors = [];
        $ap_data = SnmpQuery::walk('AI-AP-MIB::aiAPSerialNum')->table(1);

        $total_aps = count($ap_data);

        $oid = '.1.3.6.1.4.1.14823.2.3.3.1.2.1.1.4';

        $sensors[] = new WirelessSensor('ap-count', $this->getDeviceId(), $oid, 'aruba-instant', 'total-aps', 'Total APs', $total_aps);

        return $sensors;
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency(): array
    {
        $sn = SnmpQuery::cache()->walk('AI-AP-MIB::aiAPSerialNum')->table(1);

        return SnmpQuery::walk('AI-AP-MIB::aiRadioChannel')
            ->mapTable(function ($data, $aiRadioAPMACAddress, $aiRadioIndex) use ($sn) {
                $mac = Mac::parse($aiRadioAPMACAddress);

                return new WirelessSensor(
                    'frequency',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.14823.2.3.3.1.2.2.1.4.' . $mac->oid() . '.' . $aiRadioIndex,
                    'aruba-instant',
                    $mac->hex() . ".$aiRadioIndex",
                    sprintf('%s Radio %s', $sn[$aiRadioAPMACAddress]['AI-AP-MIB::aiAPSerialNum'], $aiRadioIndex),
                    WirelessSensor::channelToFrequency($this->decodeChannel($data['AI-AP-MIB::aiRadioChannel']))
                );
            })->all();
    }

    /**
     * Discover wireless noise floor. This is in dBm/Hz. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor(): array
    {
        $sn = SnmpQuery::cache()->walk('AI-AP-MIB::aiAPSerialNum')->table(1);

        return SnmpQuery::walk('AI-AP-MIB::aiRadioNoiseFloor')
            ->mapTable(function ($data, $aiRadioAPMACAddress, $aiRadioIndex) use ($sn) {
                $mac = Mac::parse($aiRadioAPMACAddress);

                return new WirelessSensor(
                    'noise-floor',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.14823.2.3.3.1.2.2.1.6.' . $mac->oid() . '.' . $aiRadioIndex,
                    'aruba-instant',
                    $mac->hex() . ".$aiRadioIndex",
                    sprintf('%s Radio %s', $sn[$aiRadioAPMACAddress]['AI-AP-MIB::aiAPSerialNum'], $aiRadioIndex),
                    $data['AI-AP-MIB::aiRadioNoiseFloor'] * -1,
                    -1,
                );
            })->all();
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower(): array
    {
        $sn = SnmpQuery::cache()->walk('AI-AP-MIB::aiAPSerialNum')->table(1);

        return SnmpQuery::walk('AI-AP-MIB::aiRadioTransmitPower')
            ->mapTable(function ($data, $aiRadioAPMACAddress, $aiRadioIndex) use ($sn) {
                $mac = Mac::parse($aiRadioAPMACAddress);

                return new WirelessSensor(
                    'power',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.14823.2.3.3.1.2.2.1.5.' . $mac->oid() . '.' . $aiRadioIndex,
                    'aruba-instant',
                    $mac->hex() . ".$aiRadioIndex",
                    sprintf('%s Radio %s: Tx Power', $sn[$aiRadioAPMACAddress]['AI-AP-MIB::aiAPSerialNum'], $aiRadioIndex),
                    $data['AI-AP-MIB::aiRadioTransmitPower'],
                );
            })->all();
    }

    /**
     * Discover wireless utilization.  This is in %. Type is utilization.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessUtilization(): array
    {
        $sn = SnmpQuery::cache()->walk('AI-AP-MIB::aiAPSerialNum')->table(1);

        return SnmpQuery::walk('AI-AP-MIB::aiRadioUtilization64')
            ->mapTable(function ($data, $aiRadioAPMACAddress, $aiRadioIndex) use ($sn) {
                $mac = Mac::parse($aiRadioAPMACAddress);

                return new WirelessSensor(
                    'utilization',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.14823.2.3.3.1.2.2.1.8.' . $mac->oid() . '.' . $aiRadioIndex,
                    'aruba-instant',
                    $mac->hex() . ".$aiRadioIndex",
                    sprintf('%s Radio %s', $sn[$aiRadioAPMACAddress]['AI-AP-MIB::aiAPSerialNum'], $aiRadioIndex),
                    $data['AI-AP-MIB::aiRadioUtilization64'],
                );
            })->all();
    }

    protected function decodeChannel($channel): int
    {
        // Trim off everything not a digit, like channel "116e"
        $channel = Number::cast(preg_replace("/\D/", '', $channel));

        return $channel & 255; // mask off the channel width information
    }

    /**
     * Poll wireless frequency as MHz
     * The returned array should be sensor_id => value pairs
     *
     * @param  array  $sensors  Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessFrequency(array $sensors)
    {
        return $this->pollWirelessChannelAsFrequency($sensors, [$this, 'decodeChannel']);
    }

    /**
     * Poll wireless clients
     * The returned array should be sensor_id => value pairs
     *
     * @param  array  $sensors  Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessClients(array $sensors)
    {
        $data = [];
        if (! empty($sensors)) {
            if (version_compare($this->getDevice()->version, '8.4.0.0', '>=')) {
                // version is at least 8.4.0.0
                $oids = Arr::pluck($sensors, 'sensor_oids.0', 'sensor_id');

                $snmp_data = SnmpQuery::numeric()->get($oids)->values();

                foreach ($oids as $id => $oid) {
                    $data[$id] = $snmp_data[$oid] ?? null;
                }
            } else {
                // version is lower than 8.4.0.0
                if (count($sensors) == 1) {
                    $client_data = SnmpQuery::walk('AI-AP-MIB::aiClientMACAddress')->values();
                    $data[$sensors[0]['sensor_id']] = count($client_data);
                }
            }
        }

        return $data;
    }

    /**
     * Poll AP Count
     * The returned array should be sensor_id => value pairs
     *
     * @param  array  $sensors  Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessApCount(array $sensors)
    {
        $data = [];
        if (! empty($sensors) && count($sensors) == 1) {
            $ap_data = SnmpQuery::walk('AI-AP-MIB::aiAPSerialNum')->values();
            $data[$sensors[0]['sensor_id']] = count($ap_data);
        }

        return $data;
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;

        $ai_ig_data = SnmpQuery::walk('AI-AP-MIB::aiInfoGroup')->table(1);
        $master_ip = $ai_ig_data[0]['AI-AP-MIB::aiMasterIPAddress'] ?? null;
        if ($master_ip) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1,
                'entPhysicalDescr' => $ai_ig_data[0]['AI-AP-MIB::aiVirtualControllerIPAddress'],
                'entPhysicalClass' => 'chassis',
                'entPhysicalName' => $ai_ig_data[0]['AI-AP-MIB::aiVirtualControllerName'],
                'entPhysicalModelName' => 'Instant Virtual Controller Cluster',
                'entPhysicalSerialNum' => $ai_ig_data[0]['AI-AP-MIB::aiVirtualControllerKey'],
                'entPhysicalMfgName' => 'Aruba',
            ]));
        }

        $index = 2;
        $ap_data = SnmpQuery::cache()->walk('AI-AP-MIB::aiAccessPointTable')->table(1);
        foreach ($ap_data as $mac => $entry) {
            $type = $master_ip == $entry['AI-AP-MIB::aiAPIPAddress'] ? 'Master' : 'Member';
            $model = $entry['AI-AP-MIB::aiAPModel'] ?? null;
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $index++,
                'entPhysicalDescr' => $entry['AI-AP-MIB::aiAPMACAddress'],
                'entPhysicalName' => sprintf('%s %s Cluster %s', $entry['AI-AP-MIB::aiAPName'], $entry['AI-AP-MIB::aiAPIPAddress'], $type),
                'entPhysicalClass' => 'other',
                'entPhysicalContainedIn' => 1,
                'entPhysicalSerialNum' => $entry['AI-AP-MIB::aiAPSerialNum'],
                'entPhysicalModelName' => explode('::', $model, 2)[1] ?? $model,
                'entPhysicalMfgName' => 'Aruba',
                'entPhysicalVendorType' => 'accessPoint',
                'entPhysicalSoftwareRev' => $this->getDevice()->version,
            ]));
        }

        return $inventory;
    }
}
