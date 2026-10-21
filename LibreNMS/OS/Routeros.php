<?php

/**
 * Routeros.php
 *
 * Mikrotik RouterOS
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
 * @copyright  2025 Peca Nesovanovic
 * @copyright  2025 Tony Murray
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\EntPhysical;
use App\Models\PortVlan;
use App\Models\Qos;
use App\Models\Transceiver;
use App\Models\Vlan;
use Illuminate\Support\Collection;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Discovery\Neighbors\Neighbor;
use LibreNMS\Discovery\Neighbors\NeighborParser;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\QosDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCcqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCellDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSinrDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\Interfaces\Discovery\VlanPortDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Interfaces\Polling\QosPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Number;
use SnmpQuery;

class Routeros extends OS implements
    VlanDiscovery,
    OSPolling,
    VlanPortDiscovery,
    QosDiscovery,
    QosPolling,
    TransceiverDiscovery,
    WirelessCcqDiscovery,
    WirelessCellDiscovery,
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessDistanceDiscovery,
    WirelessRsrqDiscovery,
    WirelessRsrpDiscovery,
    WirelessSinrDiscovery,
    WirelessQualityDiscovery
{
    private Collection $qosIdxToParent;

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessCcq()
    {
        $sensors = [];

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlApTable');
        foreach ($data as $index => $entry) {
            // skip sensors with no data (nv2 should report 1 client, but doesn't report ccq)
            if ($entry['mtxrWlApClientCount'] > 0 && $entry['mtxrWlApOverallTxCCQ'] == 0) {
                continue;
            }
            $freq = $entry['mtxrWlApFreq'] ? substr((string) $entry['mtxrWlApFreq'], 0, 1) . 'G' : 'SSID';

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Ccq,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.10.' . $index,
                'mikrotik',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'],
                $entry['mtxrWlApOverallTxCCQ']
            );
        }

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlStatTable');
        foreach ($data as $index => $entry) {
            $freq = $entry['mtxrWlStatFreq'] ? substr((string) $entry['mtxrWlStatFreq'], 0, 1) . 'G' : 'SSID';
            if (empty($entry['mtxrWlStatTxCCQ']) && empty($entry['mtxrWlStatRxCCQ'])) {
                continue;
            }

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Ccq,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.1.1.9.' . $index,
                'mikrotik-tx-ccq',
                $index,
                "$freq: " . $entry['mtxrWlStatSsid'] . ' Tx',
                $entry['mtxrWlStatTxCCQ']
            );
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Ccq,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.1.1.10.' . $index,
                'mikrotik-rx-ccq',
                $index,
                "$freq: " . $entry['mtxrWlStatSsid'] . ' Rx',
                $entry['mtxrWlStatRxCCQ']
            );
        }

        return $sensors;
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $sensors = [];
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlApTable');
        foreach ($data as $index => $entry) {
            $freq = $entry['mtxrWlApFreq'] ? substr((string) $entry['mtxrWlApFreq'], 0, 1) . 'G' : 'SSID';

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Clients,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.6.' . $index,
                'mikrotik',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'],
                $entry['mtxrWlApClientCount']
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
        $sensors = [];
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlApTable');
        foreach ($data as $index => $entry) {
            if ($entry['mtxrWlApFreq'] === '0') {
                continue;
            }
            $freq = substr((string) $entry['mtxrWlApFreq'], 0, 1) . 'G';
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Frequency,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.7.' . $index,
                'mikrotik',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'],
                $entry['mtxrWlApFreq']
            );
        }

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlStatTable');
        foreach ($data as $index => $entry) {
            if ($entry['mtxrWlStatFreq'] === '0') {
                continue;
            }
            $freq = substr((string) $entry['mtxrWlStatFreq'], 0, 1) . 'G';
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Frequency,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.1.1.7.' . $index,
                'mikrotik',
                $index,
                "$freq: " . $entry['mtxrWlStatSsid'],
                $entry['mtxrWlStatFreq']
            );
        }

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWl60GTable');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Frequency,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.8.1.6.' . $index,
                'mikrotik-60g',
                $index,
                '60G: ' . $entry['mtxrWl60GSsid'],
                $entry['mtxrWl60GFreq']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless Rssi.  This is in Dbm. Type is Dbm.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRssi()
    {
        $sensors = [];
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWl60GTable');

        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rssi,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.8.1.12.' . $index,
                'mikrotik',
                $index,
                '60G: ' . $entry['mtxrWl60GSsid'],
                $entry['mtxrWl60GRssi']
            );
        }

        foreach ($this->getCacheTable('MIKROTIK-MIB::mtxrLTEModemTable') as $index => $entry) {
            // the modem reports 0 when it has no RSSI (lTAP mini)
            if (! isset($entry['mtxrLTEModemSignalRSSI']) || (float) $entry['mtxrLTEModemSignalRSSI'] == 0) {
                continue;
            }

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rssi,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.16.1.1.2.' . $index,
                'routeros',
                $index,
                $this->lteName($index) . ': Signal RSSI',
                $entry['mtxrLTEModemSignalRSSI']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless Quality.  This is in Dbm. Type is Dbm.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessQuality()
    {
        $sensors = [];
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWl60GTable');

        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Quality,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.8.1.8.' . $index,
                'mikrotik',
                $index,
                '60G: ' . $entry['mtxrWl60GSsid'],
                $entry['mtxrWl60GSignal']
            );
        }

        return $sensors;
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessNoiseFloor()
    {
        $sensors = [];
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlApTable');

        foreach ($data as $index => $entry) {
            $freq = $entry['mtxrWlApFreq'] ? substr((string) $entry['mtxrWlApFreq'], 0, 1) . 'G' : 'SSID';

            $sensors[] = new WirelessSensor(
                WirelessSensorType::NoiseFloor,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.9.' . $index,
                'mikrotik',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'],
                $entry['mtxrWlApNoiseFloor']
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
        $sensors = [];

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlApTable');
        foreach ($data as $index => $entry) {
            if ($entry['mtxrWlApTxRate'] === '0' && $entry['mtxrWlApRxRate'] === '0') {
                continue;  // no data
            }

            $freq = $entry['mtxrWlApFreq'] ? substr((string) $entry['mtxrWlApFreq'], 0, 1) . 'G' : 'SSID';
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.2.' . $index,
                'mikrotik-tx',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'] . ' Tx',
                $entry['mtxrWlApTxRate']
            );
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.3.' . $index,
                'mikrotik-rx',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'] . ' Rx',
                $entry['mtxrWlApRxRate']
            );
        }

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlStatTable');
        foreach ($data as $index => $entry) {
            if ($entry['mtxrWlStatTxRate'] === '0' && $entry['mtxrWlStatRxRate'] === '0') {
                continue;  // no data
            }
            $freq = $entry['mtxrWlStatFreq'] ? substr((string) $entry['mtxrWlStatFreq'], 0, 1) . 'G' : 'SSID';
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.1.1.2.' . $index,
                'mikrotik-tx',
                $index,
                "$freq: " . $entry['mtxrWlStatSsid'] . ' Tx',
                $entry['mtxrWlStatTxRate']
            );
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.1.1.3.' . $index,
                'mikrotik-rx',
                $index,
                "$freq: " . $entry['mtxrWlStatSsid'] . ' Rx',
                $entry['mtxrWlStatRxRate']
            );
        }

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWl60GTable');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.8.1.13.' . $index,
                'mikrotik-60g-tx',
                $index,
                '60G: ' . $entry['mtxrWl60GSsid'],
                $entry['mtxrWl60GPhyRate'],
                1000000
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless distance.  This is in Kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessDistance()
    {
        $sensors = [];

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWl60GStaTable');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Distance,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.9.1.10.' . $index,
                'mikrotik',
                $index,
                '60G: Sta > ' . $entry['mtxrWl60GStaRemote'],
                $entry['mtxrWl60GStaDistance'],
                1,
                100000
            );
        }

        return $sensors;
    }

    /**
     * Discover LTE RSRQ (and 5G NR RSRQ on RouterOS versions that report it).  This is in dB.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRsrq()
    {
        return $this->discoverLteSignal(WirelessSensorType::Rsrq, [
            // RouterOS 7.2x adds RSRQ with one decimal, same sensor index so history is kept after an upgrade
            'mtxrLTEModemSignalRSRQD10' => ['25', '', 'Signal RSRQ', 10],
            'mtxrLTEModemSignalRSRQ' => ['3', '', 'Signal RSRQ', 1],
            'mtxrLTEModemNrRSRQ' => ['23', 'nr.', 'NR Signal RSRQ', 10],
        ]);
    }

    /**
     * Discover LTE RSRP (and 5G NR RSRP on RouterOS versions that report it).  This is in dBm.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRsrp()
    {
        return $this->discoverLteSignal(WirelessSensorType::Rsrp, [
            'mtxrLTEModemSignalRSRP' => ['4', '', 'Signal RSRP', 1],
            'mtxrLTEModemNrRSRP' => ['22', 'nr.', 'NR Signal RSRP', 1],
        ]);
    }

    /**
     * Discover LTE SINR (and 5G NR SINR on RouterOS versions that report it).  This is in dB.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSinr()
    {
        return $this->discoverLteSignal(WirelessSensorType::Sinr, [
            'mtxrLTEModemSignalSINR' => ['7', '', 'Signal SINR', 1],
            'mtxrLTEModemNrSINR' => ['24', 'nr.', 'NR Signal SINR', 1],
        ]);
    }

    /**
     * Discover the LTE serving cell id (ECI = eNB id * 256 + sector id).
     * A change in value means the modem moved to another cell.
     *
     * @return array Sensors
     */
    public function discoverWirelessCell()
    {
        $sensors = [];
        foreach ($this->getCacheTable('MIKROTIK-MIB::mtxrLTEModemTable') as $index => $entry) {
            if (! isset($entry['mtxrLTEModemCellId'])) {
                continue;
            }

            // MIB display hint renders the HexInt as hex, the poller fetches it numerically
            $cellId = $entry['mtxrLTEModemCellId'];
            $cellId = ctype_xdigit((string) $cellId) ? hexdec($cellId) : (int) $cellId;
            if ($cellId < 0 || $cellId >= 0xFFFFFFFF) {
                continue; // -1 (or all ones): modem does not report the cell id (seen on SXT LTE)
            }

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Cell,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.16.1.1.5.' . $index,
                'routeros',
                $index,
                $this->lteName($index) . ': Cell ID',
                $cellId
            );
        }

        return $sensors;
    }

    /**
     * @param  array<string, array{string, string, string, int}>  $columns  column => [oid leaf, index prefix, description, divisor]
     * @return array<WirelessSensor>
     */
    private function discoverLteSignal(WirelessSensorType $type, array $columns): array
    {
        $sensors = [];
        foreach ($this->getCacheTable('MIKROTIK-MIB::mtxrLTEModemTable') as $index => $entry) {
            $found = [];
            foreach ($columns as $column => [$leaf, $prefix, $descr, $divisor]) {
                // the first available column wins when several map to the same sensor index
                if (isset($found[$prefix])) {
                    continue;
                }

                // newer columns (5G NR) are absent on older RouterOS or report 0 (NR RSRQ: '.0') on LTE-only modems
                if (! isset($entry[$column]) || ($prefix !== '' && (float) $entry[$column] == 0)) {
                    continue;
                }

                // the initial value is stored as-is: IDiv10 columns are already scaled by the MIB display hint
                // when walked by name ('-8.0'), only scale raw integers
                $current = $entry[$column];
                if ($divisor > 1 && ! str_contains((string) $current, '.')) {
                    $current /= $divisor;
                }

                $found[$prefix] = true;
                $sensors[] = new WirelessSensor(
                    $type,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.14988.1.1.16.1.1.' . $leaf . '.' . $index,
                    'routeros',
                    $prefix . $index,
                    $this->lteName($index) . ': ' . $descr,
                    $current,
                    1,
                    $divisor
                );
            }
        }

        return $sensors;
    }

    private function lteName(int|string $index): string
    {
        return $this->getCacheByIndex('MIKROTIK-MIB::mtxrInterfaceStatsName')[$index] ?? "lte$index";
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = parent::discoverEntityPhysical();
        $chassis = $inventory->firstWhere('entPhysicalClass', 'chassis')->entPhysicalIndex ?? 0;

        foreach ($this->getCacheTable('MIKROTIK-MIB::mtxrLTEModemTable') as $index => $entry) {
            $model = trim($entry['mtxrLTEModemModel'] ?? '');
            $firmware = trim($entry['mtxrLTEModemFirmware'] ?? '');
            $iccid = trim($entry['mtxrLTEModemUICC'] ?? '');
            $imsi = trim($entry['mtxrLTEModemIMSI'] ?? '');

            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $this->lteEntityIndex($index),
                'entPhysicalContainedIn' => $chassis,
                'entPhysicalClass' => 'module',
                'entPhysicalName' => $this->lteName($index),
                'entPhysicalDescr' => trim(($model ?: 'LTE') . ' modem' . ($firmware ? ", firmware $firmware" : '')),
                'entPhysicalModelName' => $model ?: null,
                'entPhysicalFirmwareRev' => $firmware ?: null,
                'entPhysicalSerialNum' => trim($entry['mtxrLTEModemIMEI'] ?? '') ?: null,
                'entPhysicalParentRelPos' => -1,
                'ifIndex' => $index,
            ]));

            if (! isset($entry['mtxrLTEModemUICC'])) {
                continue; // older RouterOS does not report the ICCID, so SIM presence is unknown
            }

            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $this->lteEntityIndex($index, true),
                'entPhysicalContainedIn' => $this->lteEntityIndex($index),
                'entPhysicalClass' => 'module',
                'entPhysicalName' => 'SIM card',
                'entPhysicalDescr' => $iccid === '' ? 'SIM card not detected' : 'SIM card' . ($imsi ? ", IMSI $imsi" : ''),
                'entPhysicalSerialNum' => $iccid ?: null,
                'entPhysicalParentRelPos' => -1,
            ]));
        }

        return $inventory;
    }

    /**
     * Inventory index for the LTE modem (or its SIM card), offset to stay clear of RouterOS ENTITY-MIB indexes
     */
    private function lteEntityIndex(int|string $ifIndex, bool $sim = false): int
    {
        return 1000000000 + (int) $ifIndex * 10 + ($sim ? 1 : 0);
    }

    /**
     * LTE modem state sensors. RouterOS reports these as free-form strings, so they are
     * normalized to numeric states here and again at poll time (includes/polling/sensors/state/routeros.inc.php).
     * SIM presence is derived from the ICCID, which works on every RouterOS version;
     * PIN and registration status only exist on newer RouterOS (MIB 2025+) and may be left empty (seen on 7.23.5).
     */
    public function discoverLteStateSensors(): void
    {
        $columns = [
            'mtxrLTEModemUICC' => ['13', 'SIM card'],
            'mtxrLTEModemRAT' => ['14', 'Access technology'],
            'mtxrLTEModemRegStatus' => ['17', 'Registration'],
            'mtxrLTEModemPinStatus' => ['18', 'SIM PIN status'],
        ];

        $state_indexes = [];
        foreach ($this->getCacheTable('MIKROTIK-MIB::mtxrLTEModemTable') as $index => $entry) {
            foreach ($columns as $column => [$leaf, $descr]) {
                // the ICCID is empty without a SIM, which is the state we want to see.
                // Other columns are empty when RouterOS does not fill them (PIN/registration status on 7.23)
                if (! isset($entry[$column]) || ($column !== 'mtxrLTEModemUICC' && trim((string) $entry[$column]) === '')) {
                    continue;
                }

                if (! isset($state_indexes[$column])) {
                    $states = self::LTE_STATES[$column];
                    create_state_index($column, array_map(fn ($value, $state) => [
                        'value' => $value,
                        'generic' => $state[1],
                        'descr' => $state[0],
                    ], array_keys($states), $states));
                    $state_indexes[$column] = true;
                }

                app('sensor-discovery')->discover(new \App\Models\Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'state',
                    'sensor_oid' => '.1.3.6.1.4.1.14988.1.1.16.1.1.' . $leaf . '.' . $index,
                    'sensor_index' => $column . '.' . $index,
                    'sensor_type' => $column,
                    'sensor_descr' => $this->lteName($index) . ': ' . $descr,
                    'sensor_current' => self::normalizeLteState($column, $entry[$column]),
                    'group' => 'LTE',
                    'entPhysicalIndex' => $this->lteEntityIndex($index, $column === 'mtxrLTEModemUICC'),
                ]));
            }
        }
    }

    /**
     * RouterBOARD firmware (RouterBOOT) is not upgraded together with RouterOS,
     * it stays behind until "/system routerboard upgrade" and a reboot.
     */
    public function discoverFirmwareStateSensor(): void
    {
        $firmware = SnmpQuery::get(['MIKROTIK-MIB::mtxrFirmwareVersion.0', 'MIKROTIK-MIB::mtxrFirmwareUpgradeVersion.0'])->values();
        $current = $firmware['MIKROTIK-MIB::mtxrFirmwareVersion.0'] ?? '';
        $upgrade = $firmware['MIKROTIK-MIB::mtxrFirmwareUpgradeVersion.0'] ?? '';

        if ($current === '' || $upgrade === '') {
            return; // CHR and x86 have no RouterBOOT
        }

        create_state_index('mtxrFirmwareUpgrade', [
            ['value' => 0, 'generic' => 0, 'descr' => 'up to date'],
            ['value' => 1, 'generic' => 0, 'descr' => 'upgrade available'],
            ['value' => 2, 'generic' => 3, 'descr' => 'unknown'],
        ]);

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'sensor_oid' => '.1.3.6.1.4.1.14988.1.1.7.4.0',
            'sensor_index' => 'mtxrFirmwareUpgrade.0',
            'sensor_type' => 'mtxrFirmwareUpgrade',
            'sensor_descr' => 'RouterBOOT firmware',
            'sensor_current' => self::firmwareState($current, $upgrade),
            'group' => 'System',
        ]));
    }

    /**
     * @return int 0 up to date, 1 upgrade available, 2 unknown
     */
    public static function firmwareState(?string $current, ?string $upgrade): int
    {
        $current = trim((string) $current);
        $upgrade = trim((string) $upgrade);

        if ($current === '' || $upgrade === '') {
            return 2;
        }

        return version_compare($upgrade, $current, '>') ? 1 : 0;
    }

    /**
     * RouterOS has no VRRP MIB, a VRRP interface is only running (ifOperStatus up) while it is master.
     * VRRP interfaces are found by their virtual MAC 00:00:5e:00:01:<vrid> (IPv4) or 00:00:5e:00:02:<vrid> (IPv6).
     * Only ports from the ports table are checked (it excludes PPPoE session interfaces), so concentrators
     * with many sessions are not walked again.
     */
    public function discoverVrrpStateSensors(): void
    {
        $ports = $this->getDevice()->ports()->where('deleted', 0)->get();

        // ifPhysAddress is filled by the ports poller, fetch it for ports that were not polled yet (new device)
        $missing = $ports->whereNull('ifPhysAddress');
        if ($missing->isNotEmpty()) {
            $macs = SnmpQuery::get($missing->map(fn ($port) => 'IF-MIB::ifPhysAddress.' . $port->ifIndex)->all())->values();
            foreach ($missing as $port) {
                $port->ifPhysAddress = Mac::parse($macs["IF-MIB::ifPhysAddress[$port->ifIndex]"] ?? null)->hex() ?: null;
            }
        }

        $ports = $ports->filter(fn ($port) => preg_match('/^00005e000[12]/', Mac::parse($port->ifPhysAddress)->hex()));

        if ($ports->isEmpty()) {
            return;
        }

        // master and backup are both normal states, which router should be master is configuration.
        // The other ifOperStatus values mean the VRRP interface can't run, e.g. its parent interface is down.
        create_state_index('routerosVrrpState', [
            ['value' => 1, 'generic' => 0, 'descr' => 'master'],
            ['value' => 2, 'generic' => 0, 'descr' => 'backup'],
            ['value' => 3, 'generic' => 3, 'descr' => 'testing'],
            ['value' => 4, 'generic' => 3, 'descr' => 'unknown'],
            ['value' => 5, 'generic' => 1, 'descr' => 'dormant'],
            ['value' => 6, 'generic' => 1, 'descr' => 'notPresent'],
            ['value' => 7, 'generic' => 1, 'descr' => 'lowerLayerDown'],
        ]);

        // IF-MIB::ifOperStatus values
        $oper_status = ['up' => 1, 'down' => 2, 'testing' => 3, 'unknown' => 4, 'dormant' => 5, 'notPresent' => 6, 'lowerLayerDown' => 7];

        foreach ($ports as $port) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => '.1.3.6.1.2.1.2.2.1.8.' . $port->ifIndex,
                'sensor_index' => 'vrrp.' . $port->ifIndex,
                'sensor_type' => 'routerosVrrpState',
                'sensor_descr' => 'VRRP ' . $port->ifDescr,
                'sensor_current' => $oper_status[$port->ifOperStatus?->value] ?? null,
                'group' => 'VRRP',
                'entPhysicalIndex' => $port->ifIndex,
                'entPhysicalIndex_measured' => 'ports',
            ]));
        }
    }

    /**
     * Active PPPoE sessions, RouterOS answers CISCO-AAA-SESSION-MIB::casnActiveTableEntries.
     * Every RouterOS answers 0 without a PPP server, so the sensor is only created once there are sessions,
     * an existing sensor is kept at 0 so it can alert when all sessions drop.
     */
    public function discoverPppoeSessionSensor(): void
    {
        $sessions = SnmpQuery::mibDir('cisco')->get('CISCO-AAA-SESSION-MIB::casnActiveTableEntries.0')->value();

        if (! is_numeric($sessions)) {
            return;
        }

        $exists = $this->getDevice()->sensors()
            ->where('sensor_class', 'count')
            ->where('sensor_type', 'routeros')
            ->where('sensor_index', 'casnActiveTableEntries.0')
            ->exists();

        if ($sessions == 0 && ! $exists) {
            return;
        }

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'count',
            'sensor_oid' => '.1.3.6.1.4.1.9.9.150.1.1.1.0',
            'sensor_index' => 'casnActiveTableEntries.0',
            'sensor_type' => 'routeros',
            'sensor_descr' => 'PPPoE Session Count',
            'sensor_current' => $sessions,
        ]));
    }

    /**
     * state value => [description, generic state (0 ok, 1 warning, 2 critical, 3 unknown)]
     */
    public const LTE_STATES = [
        'mtxrLTEModemUICC' => [
            0 => ['not detected', 2],
            1 => ['present', 0],
        ],
        'mtxrLTEModemRAT' => [
            0 => ['unknown', 3],
            2 => ['2G', 0],
            3 => ['3G', 0],
            4 => ['LTE', 0],
            5 => ['5G NSA', 0],
            6 => ['5G SA', 0],
        ],
        'mtxrLTEModemRegStatus' => [
            0 => ['unknown', 3],
            1 => ['registered', 0],
            2 => ['roaming', 0],
            3 => ['searching', 1],
            4 => ['denied', 2],
            5 => ['not registered', 2],
        ],
        'mtxrLTEModemPinStatus' => [
            0 => ['SIM not inserted', 2],
            1 => ['ok', 0],
            2 => ['PIN required', 2],
            3 => ['PUK required', 2],
            4 => ['unknown', 3],
        ],
    ];

    /**
     * Map the raw RouterOS string of an LTE state column to its numeric state.
     */
    public static function normalizeLteState(string $column, mixed $value): ?int
    {
        $value = strtolower(trim((string) $value));

        return match ($column) {
            'mtxrLTEModemUICC' => $value === '' ? 0 : 1,
            'mtxrLTEModemRAT' => match (true) {
                str_contains($value, 'nsa') || (str_contains($value, 'lte') && preg_match('/nr|5g/', $value)) => 5,
                (bool) preg_match('/nr|5g/', $value) => 6,
                (bool) preg_match('/lte|4g|e-utran/', $value) => 4,
                (bool) preg_match('/umts|wcdma|hspa|utran|3g/', $value) => 3,
                (bool) preg_match('/gsm|gprs|edge|2g/', $value) => 2,
                default => 0,
            },
            'mtxrLTEModemRegStatus' => match (true) {
                // before 'not registered': 'not registered, searching' is a modem that is re-attaching
                str_contains($value, 'search') => 3,
                (bool) preg_match('/not registered|unregistered|not-registered/', $value) => 5,
                str_contains($value, 'denied') => 4,
                str_contains($value, 'roaming') => 2,
                str_contains($value, 'registered') => 1,
                default => 0,
            },
            'mtxrLTEModemPinStatus' => match (true) {
                (bool) preg_match('/not inserted|not present|no sim|absent/', $value) => 0,
                str_contains($value, 'puk') => 3,
                (bool) preg_match('/\bpin\b/', $value) => 2,
                (bool) preg_match('/^ok$|ready|no password/', $value) => 1,
                default => 4,
            },
            default => null,
        };
    }

    public function pollOS(DataStorageInterface $datastore): void
    {
        $leases = snmp_get($this->getDeviceArray(), 'mtxrDHCPLeaseCount.0', '-OQv', 'MIKROTIK-MIB');

        if (is_numeric($leases)) {
            $rrd_def = RrdDefinition::make()->addDataset('leases', 'GAUGE', 0);

            $fields = [
                'leases' => $leases,
            ];

            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($this->getDeviceArray(), 'routeros_leases', $tags, $fields);
            $this->enableGraph('routeros_leases');
        }

        $pppoe_sessions = snmp_get($this->getDeviceArray(), '1.3.6.1.4.1.9.9.150.1.1.1.0', '-OQv', '', '');

        if (is_numeric($pppoe_sessions)) {
            $rrd_def = RrdDefinition::make()->addDataset('pppoe_sessions', 'GAUGE', 0);

            $fields = [
                'pppoe_sessions' => $pppoe_sessions,
            ];

            $tags = ['rrd_def' => $rrd_def];
            $datastore->put($this->getDeviceArray(), 'routeros_pppoe_sessions', $tags, $fields);
            $this->enableGraph('routeros_pppoe_sessions');
        }
    }

    public function discoverQos(): Collection
    {
        $this->qosIdxToParent = new Collection();
        $qos = new Collection();

        $qos = $qos->concat(\SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleTable')->mapTable(fn ($data, $qosIndex) => new Qos([
            'device_id' => $this->getDeviceId(),
            'type' => 'routeros_simple',
            'title' => $data['MIKROTIK-MIB::mtxrQueueSimpleName'],
            'snmp_idx' => $qosIndex,
            'rrd_id' => $data['MIKROTIK-MIB::mtxrQueueSimpleName'],
            'ingress' => 1,
            'egress' => 1,
        ])));

        $this->qosIdxToParent->put('routeros_tree', new Collection());
        $qos = $qos->concat(\SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueTreeTable')->mapTable(function ($data, $qosIndex) {
            $thisQos = new Qos([
                'device_id' => $this->getDeviceId(),
                'port_id' => PortCache::getIdFromIfIndex(hexdec((string) $data['MIKROTIK-MIB::mtxrQueueTreeParentIndex']), $this->getDevice()),
                'type' => 'routeros_tree',
                'title' => $data['MIKROTIK-MIB::mtxrQueueTreeName'],
                'snmp_idx' => $qosIndex,
                'rrd_id' => $data['MIKROTIK-MIB::mtxrQueueTreeName'],
                'ingress' => 0,
                'egress' => 1,
            ]);

            // Save this child -> parent index into the collection for use in setQosParents();
            $this->qosIdxToParent->get('routeros_tree')->put($qosIndex, hexdec((string) $data['MIKROTIK-MIB::mtxrQueueTreeParentIndex']));

            return $thisQos;
        }));

        return $qos;
    }

    public function setQosParents($qos)
    {
        $qos->each(function (Qos $thisQos, int $key) use ($qos): void {
            $qosParentMap = $this->qosIdxToParent->get($thisQos->type);
            if (! $qosParentMap) {
                // Parent data does not exist
                return;
            }

            $parent_idx = $qosParentMap->get($thisQos->snmp_idx);

            if ($parent_idx) {
                $parent = $qos->where('type', $thisQos->type)->where('snmp_idx', $parent_idx)->first();

                if ($parent) {
                    $parent_id = $parent->qos_id;
                } else {
                    $parent_id = null;
                }
            } else {
                $parent_id = null;
            }

            $thisQos->parent_id = $parent_id;
            $thisQos->save();
        });
    }

    public function pollQos($qos)
    {
        $poll_time = time();
        $treeNames = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueTreeName')->table(1);
        $treeBytes = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueTreeHCBytes')->table(1);
        // Packet counters are not updating
        //$treePackets = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueTreePackets')->table(1);
        $treeDrops = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueTreeDropped')->table(1);

        $simpleNames = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleName')->table(1);
        $simpleBytesIn = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleBytesIn')->table(1);
        $simpleBytesOut = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleBytesOut')->table(1);
        // Packet counters are not updating
        //$simplePacketsIn = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimplePacketsIn')->table(1);
        //$simplePacketsOut = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimplePacketsOut')->table(1);
        $simpleDropsIn = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleDroppedIn')->table(1);
        $simpleDropsOut = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleDroppedOut')->table(1);

        $qos->each(function (Qos $thisQos, int $key) use ($poll_time, $treeNames, $treeBytes, $treeDrops, $simpleNames, $simpleBytesIn, $simpleBytesOut, $simpleDropsIn, $simpleDropsOut): void {
            switch ($thisQos->type) {
                case 'routeros_tree':
                    if (! array_key_exists($thisQos->snmp_idx, $treeNames)) {
                        d_echo('Ignoring queue tree ' . $thisQos->rrd_id . " because was not found SNMP\n");
                        break;
                    }
                    if ($treeNames[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueTreeName'] != $thisQos->rrd_id) {
                        d_echo('Ignoring queue tree ' . $thisQos->rrd_id . " because it does not match SNMP\n");
                        break;
                    }
                    d_echo('Updating queue tree ' . $thisQos->rrd_id . "\n");
                    $thisQos->last_polled = $poll_time;
                    $thisQos->last_bytes_in = null;
                    $thisQos->last_bytes_out = $treeBytes[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueTreeHCBytes'];
                    $thisQos->last_bytes_drop_in = null;
                    $thisQos->last_bytes_drop_out = null;
                    $thisQos->last_packets_in = null;
                    // Packet counters are not updating
                    //$thisQos->last_packets_out = $treePackets[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueTreePackets'];
                    $thisQos->last_packets_out = null;
                    $thisQos->last_packets_drop_in = null;
                    $thisQos->last_packets_drop_out = $treeDrops[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueTreeDropped'];
                    break;
                case 'routeros_simple':
                    if (! array_key_exists($thisQos->snmp_idx, $simpleNames)) {
                        d_echo('Ignoring simple tree ' . $thisQos->rrd_id . " because was not found SNMP\n");
                        break;
                    }
                    if ($simpleNames[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimpleName'] != $thisQos->rrd_id) {
                        d_echo('Ignoring simple tree ' . $thisQos->rrd_id . " because it does not match SNMP\n");
                        break;
                    }
                    d_echo('Updating simple tree ' . $thisQos->rrd_id . "\n");
                    $thisQos->last_polled = $poll_time;
                    $thisQos->last_bytes_in = $simpleBytesIn[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimpleBytesIn'];
                    $thisQos->last_bytes_out = $simpleBytesOut[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimpleBytesOut'];
                    $thisQos->last_bytes_drop_in = null;
                    $thisQos->last_bytes_drop_out = null;
                    // Packet counters are not updating
                    //$thisQos->last_packets_in = $simplePacketsIn[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimplePacketsIn'];
                    //$thisQos->last_packets_out = $simplePacketsOut[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimplePacketsOut'];
                    $thisQos->last_packets_in = null;
                    $thisQos->last_packets_out = null;
                    $thisQos->last_packets_drop_in = $simpleDropsIn[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimpleDroppedIn'];
                    $thisQos->last_packets_drop_out = $simpleDropsOut[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimpleDroppedOut'];
                    break;
                default:
                    echo 'Queue type ' . $thisQos->type . " has not been implmeneted in LibreNMS/OS/Routeros.php\n";
            }
        });
    }

    public function discoverTransceivers(): Collection
    {
        return \SnmpQuery::walk('MIKROTIK-MIB::mtxrOpticalTable')->mapTable(function ($data, $ifIndex) {
            // RouterOS >= 7.24 lists every SFP cage, including empty ones
            if (isset($data['MIKROTIK-MIB::mtxrOpticalModulePresent'])) {
                if (! in_array($data['MIKROTIK-MIB::mtxrOpticalModulePresent'], ['true', '1'], true)) {
                    return null;
                }
            } elseif (isset($data['MIKROTIK-MIB::mtxrOpticalVendorSerial']) && $data['MIKROTIK-MIB::mtxrOpticalVendorSerial'] === '' && empty($data['MIKROTIK-MIB::mtxrOpticalVendorName'])) {
                return null;
            }

            $wavelength = isset($data['MIKROTIK-MIB::mtxrOpticalWavelength']) && $data['MIKROTIK-MIB::mtxrOpticalWavelength'] != '.00' ? Number::cast($data['MIKROTIK-MIB::mtxrOpticalWavelength']) : null;

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'vendor' => $data['MIKROTIK-MIB::mtxrOpticalVendorName'] ?? null,
                'serial' => $data['MIKROTIK-MIB::mtxrOpticalVendorSerial'] ?? null,
                'wavelength' => $wavelength == 65535 ? null : $wavelength, // NA value = 65535.00
                'entity_physical_index' => $ifIndex,
            ]);
        })->filter();
    }

    public function discoverVlans(): Collection
    {
        $vlans = parent::discoverVlans(); // Q-BRIDGE-MIB
        if ($vlans->isNotEmpty()) {
            return $vlans;
        }

        $scripts = SnmpQuery::cache()->walk('MIKROTIK-MIB::mtxrScriptName')->table();
        $scriptIndex = array_flip($scripts['MIKROTIK-MIB::mtxrScriptName'] ?? [])['LNMS_vlans'] ?? null;

        if (! empty($scriptIndex)) {
            $data = SnmpQuery::cache()->get('MIKROTIK-MIB::mtxrScriptRunOutput.' . $scriptIndex)->value();
            $oldId = 0;

            foreach (preg_split("/((\r?\n)|(\r\n?))/", (string) $data) as $line) {
                if (! empty($line)) {
                    [$mtType, $vlanId, $mtData] = array_map(trim(...), explode(',', $line));

                    if ($mtType == 'N') {
                        $vlanNames[$vlanId] = $mtData;
                        continue;
                    }

                    if ($oldId != $vlanId) {
                        $oldId = $vlanId;
                        $vlans->push(new Vlan([
                            'vlan_vlan' => $vlanId,
                            'vlan_domain' => 1,
                            'vlan_name' => $vlanNames[$vlanId] ?? 'Vlan_' . $vlanId,
                        ]));
                    }
                }
            }
        }

        return $vlans;
    }

    public function discoverVlanPorts(Collection $vlans): Collection
    {
        $ports = parent::discoverVlanPorts($vlans); // Q-BRIDGE-MIB
        if ($ports->isNotEmpty()) {
            return $ports;
        }

        $scripts = SnmpQuery::cache()->walk('MIKROTIK-MIB::mtxrScriptName')->table();
        $scriptIndex = array_flip($scripts['MIKROTIK-MIB::mtxrScriptName'] ?? [])['LNMS_vlans'] ?? null;

        if (! empty($scriptIndex)) {
            $data = SnmpQuery::cache()->get('MIKROTIK-MIB::mtxrScriptRunOutput.' . $scriptIndex)->value();
            $ifNames = array_flip(SnmpQuery::cache()->walk('IF-MIB::ifName')->pluck());

            foreach (preg_split("/((\r?\n)|(\r\n?))/", (string) $data) as $line) {
                if (! empty($line)) {
                    [$mtType, $vlanId, $mtData] = array_map(trim(...), explode(',', $line));

                    if ($mtType == 'N') { // type 'N' is for vlan name, skip from processing
                        continue;
                    }

                    $ifIndex = $ifNames[$mtData] ?? 0;

                    $ports->push(new PortVlan([
                        'vlan' => $vlanId,
                        'baseport' => $this->bridgePortFromIfIndex($ifIndex),
                        'untagged' => ($mtType == 'U') ? 1 : 0,
                        'port_id' => PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId()) ?? 0, // ifIndex from device
                    ]));
                }
            }
        }

        return $ports;
    }

    protected function discoverLldpNeighbors(): Collection
    {
        // RouterOS before 7.7 reports all neighbors on port 0
        $portZeroEntries = SnmpQuery::hideMib()->cache()->walk('LLDP-MIB::lldpRemTable')->table(3)[0][0] ?? [];

        return empty($portZeroEntries)
            ? parent::discoverLldpNeighbors()
            : $this->discoverMikrotikLldpNeighbors($portZeroEntries);
    }

    /**
     * RouterOS uses the ifIndex as the LLDP port number
     *
     * @return array<int|string, int|string>
     */
    protected function lldpBridgePortIfIndexes(): array
    {
        return [];
    }

    /**
     * The port of neighbors reported on port 0 is in the MIKROTIK-MIB neighbor table
     *
     * @param  array<int|string, array<string, mixed>>  $entries  lldpRemTable entries indexed by lldpRemIndex
     * @return Collection<int, Neighbor>
     */
    private function discoverMikrotikLldpNeighbors(array $entries): Collection
    {
        $interfaceIds = SnmpQuery::hideMib()->walk('MIKROTIK-MIB::mtxrNeighborInterfaceID')->pluck();
        $interfaceNames = SnmpQuery::hideMib()->walk('MIKROTIK-MIB::mtxrInterfaceStatsName')->pluck();

        return (new Collection($entries))->filter(fn ($entry) => is_array($entry))->map(function (array $entry, $index) use ($interfaceIds, $interfaceNames) {
            $ifName = isset($interfaceIds[$index]) ? $interfaceNames[hexdec((string) $interfaceIds[$index])] ?? null : null;

            return NeighborParser::fromLldpRemEntry($entry, $this->lldpLocalPorts()->byDescr($ifName)?->port_id);
        })->values();
    }
}
