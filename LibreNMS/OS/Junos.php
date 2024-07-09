<?php
/*
 * Junos.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Port;
use App\Models\Sla;
use App\Models\Transceiver;
use App\Models\TransceiverMetric;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\SlaDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Interfaces\Polling\SlaPolling;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class Junos extends \LibreNMS\OS implements SlaDiscovery, OSPolling, SlaPolling, TransceiverDiscovery
{
    public function discoverOS(Device $device): void
    {
        $data = snmp_get_multi($this->getDeviceArray(), [
            'JUNIPER-MIB::jnxBoxDescr.0',
            'JUNIPER-MIB::jnxBoxSerialNo.0',
            'JUNIPER-VIRTUALCHASSIS-MIB::jnxVirtualChassisMemberSWVersion.0',
            'HOST-RESOURCES-MIB::hrSWInstalledName.2',
        ], '-OQUs');

        preg_match('/Juniper Networks, Inc. (?<hardware>\S+) .* kernel JUNOS (?<version>[^, ]+)[, ]/', $device->sysDescr, $parsed);
        if (isset($data[2]['hrSWInstalledName'])) {
            preg_match('/\[(.+)]/', $data[2]['hrSWInstalledName'], $parsedVersion);
        }

        $device->hardware = $data[0]['jnxBoxDescr'] ?? (isset($parsed['hardware']) ? 'Juniper ' . strtoupper($parsed['hardware']) : null);
        $device->serial = $data[0]['jnxBoxSerialNo'] ?? null;
        $device->version = $data[0]['jnxVirtualChassisMemberSWVersion'] ?? $parsedVersion[1] ?? $parsed['version'] ?? null;
    }

    public function pollOS(DataStorageInterface $datastore): void
    {
        $data = snmp_get_multi($this->getDeviceArray(), 'jnxJsSPUMonitoringCurrentFlowSession.0', '-OUQs', 'JUNIPER-SRX5000-SPU-MONITORING-MIB');

        if (is_numeric($data[0]['jnxJsSPUMonitoringCurrentFlowSession'] ?? null)) {
            $datastore->put($this->getDeviceArray(), 'junos_jsrx_spu_sessions', [
                'rrd_def' => RrdDefinition::make()->addDataset('spu_flow_sessions', 'GAUGE', 0),
            ], [
                'spu_flow_sessions' => $data[0]['jnxJsSPUMonitoringCurrentFlowSession'],
            ]);

            $this->enableGraph('junos_jsrx_spu_sessions');
        }
    }

    public function discoverSlas(): Collection
    {
        $slas = new Collection();
        $sla_table = snmpwalk_group($this->getDeviceArray(), 'pingCtlTable', 'DISMAN-PING-MIB', 2, snmpFlags: '-OQUstX');

        if (! empty($sla_table)) {
            $sla_table = snmpwalk_group($this->getDeviceArray(), 'jnxPingResultsRttUs', 'JUNIPER-PING-MIB', 2, $sla_table, snmpFlags: '-OQUstX');
        }

        foreach ($sla_table as $sla_key => $sla_config) {
            foreach ($sla_config as $test_key => $test_config) {
                $slas->push(new Sla([
                    'sla_nr' => hexdec(hash('crc32', $sla_key . $test_key)), // indexed by owner+test, convert to int
                    'owner' => $sla_key,
                    'tag' => $test_key,
                    'rtt_type' => $this->retrieveJuniperType($test_config['pingCtlType']),
                    'rtt' => isset($test_config['jnxPingResultsRttUs']) ? $test_config['jnxPingResultsRttUs'] / 1000 : null,
                    'status' => ($test_config['pingCtlAdminStatus'] == 'enabled') ? 1 : 0,
                    'opstatus' => ($test_config['pingCtlRowStatus'] == 'active') ? 0 : 2,
                ]));
            }
        }

        return $slas;
    }

    public function pollSlas($slas): void
    {
        $device = $this->getDeviceArray();

        // Go get some data from the device.
        $data = snmpwalk_group($device, 'pingCtlRowStatus', 'DISMAN-PING-MIB', 2);
        $data = snmpwalk_group($device, 'jnxPingLastTestResultTable', 'JUNIPER-PING-MIB', 2, $data);
        $data = snmpwalk_group($device, 'jnxPingResultsTable', 'JUNIPER-PING-MIB', 2, $data);

        // Get the needed information
        foreach ($slas as $sla) {
            $sla_nr = $sla->sla_nr;
            $rtt_type = $sla->rtt_type;
            $owner = $sla->owner;
            $test = $sla->tag;

            // Lets process each SLA

            // Use DISMAN-PING Status codes. 0=Good 2=Critical
            $sla->opstatus = $data[$owner][$test]['pingCtlRowStatus'] == '1' ? 0 : 2;

            $sla->rtt = ($data[$owner][$test]['jnxPingResultsAvgRttUs'] ?? 0) / 1000;
            $time = Carbon::parse($data[$owner][$test]['jnxPingResultsTime'] ?? null)->toDateTimeString();
            echo 'SLA : ' . $rtt_type . ' ' . $owner . ' ' . $test . '... ' . $sla->rtt . 'ms at ' . $time . "\n";

            $collected = ['rtt' => $sla->rtt];

            // Let's gather some per-type fields.
            switch ($rtt_type) {
                case 'DnsQuery':
                case 'HttpGet':
                case 'HttpGetMetadata':
                    break;
                case 'IcmpEcho':
                case 'IcmpTimeStamp':
                    $icmp = [
                        'MinRttUs' => ($data[$owner][$test]['jnxPingResultsMinRttUs'] ?? 0) / 1000,
                        'MaxRttUs' => ($data[$owner][$test]['jnxPingResultsMaxRttUs'] ?? 0) / 1000,
                        'StdDevRttUs' => ($data[$owner][$test]['jnxPingResultsStdDevRttUs'] ?? 0) / 1000,
                        'ProbeResponses' => $data[$owner][$test]['jnxPingLastTestResultProbeResponses'] ?? null,
                        'ProbeLoss' => (int) ($data[$owner][$test]['jnxPingLastTestResultSentProbes'] ?? 0) - (int) ($data[$owner][$test]['jnxPingLastTestResultProbeResponses'] ?? 0),
                    ];
                    $rrd_name = ['sla', $sla_nr, $rtt_type];
                    $rrd_def = RrdDefinition::make()
                        ->addDataset('MinRttUs', 'GAUGE', 0, 300000)
                        ->addDataset('MaxRttUs', 'GAUGE', 0, 300000)
                        ->addDataset('StdDevRttUs', 'GAUGE', 0, 300000)
                        ->addDataset('ProbeResponses', 'GAUGE', 0, 300000)
                        ->addDataset('ProbeLoss', 'GAUGE', 0, 300000);
                    $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                    app('Datastore')->put($device, 'sla', $tags, $icmp);
                    $collected = array_merge($collected, $icmp);
                    break;
                case 'NtpQuery':
                case 'UdpTimestamp':
                    break;
            }

            d_echo('The following datasources were collected for #' . $sla->sla_nr . ":\n");
            d_echo($collected);
        }
    }

    /**
     * Retrieve specific Juniper PingCtlType
     */
    private function retrieveJuniperType($rtt_type)
    {
        switch ($rtt_type) {
            case 'enterprises.2636.3.7.2.1':
                return 'IcmpTimeStamp';
            case 'enterprises.2636.3.7.2.2':
                return 'HttpGet';
            case 'enterprises.2636.3.7.2.3':
                return 'HttpGetMetadata';
            case 'enterprises.2636.3.7.2.4':
                return 'DnsQuery';
            case 'enterprises.2636.3.7.2.5':
                return 'NtpQuery';
            case 'enterprises.2636.3.7.2.6':
                return 'UdpTimestamp';
            case 'zeroDotZero':
                return 'twamp';
            default:
                return str_replace('ping', '', $rtt_type);
        }
    }

    public function discoverTransceivers(): Collection
    {
        $ifIndexToPortId = Port::query()->where('device_id', $this->getDeviceId())->select(['port_id', 'ifIndex', 'ifName'])->get()->keyBy('ifIndex');
        $entPhysical = SnmpQuery::walk('ENTITY-MIB::entityPhysical')->table(1);

        return SnmpQuery::cache()->walk('JUNIPER-DOM-MIB::jnxDomCurrentTable')->mapTable(function ($data, $ifIndex) use ($ifIndexToPortId, $entPhysical) {
            $ent = $this->findTransceiverEntityByPortName($entPhysical, $ifIndexToPortId->get($ifIndex)?->ifName);
            if (empty($ent)) {
                return null; // no module
            }

            return new Transceiver([
                'port_id' => $ifIndexToPortId->get($ifIndex)->port_id,
                'index' => $ifIndex,
                'type' => $ent['ENTITY-MIB::entPhysicalName'] ?? null,
                'vendor' => $ent['ENTITY-MIB::entPhysicalMfgName'] ?? null,
                'model' => $ent['ENTITY-MIB::entPhysicalModelName'] ?? null,
                'revision' => $ent['ENTITY-MIB::entPhysicalHardwareRev'] ?? null,
                'serial' => $ent['ENTITY-MIB::entPhysicalSerialNum'] ?? null,
                'channels' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleLaneCount'] ?? 0,
            ]);
        })->filter();
    }

    public function discoverTransceiverMetrics(Collection $transceivers): Collection
    {
        $metrics = new Collection;
        $xcData = SnmpQuery::cache()->walk('JUNIPER-DOM-MIB::jnxDomCurrentTable')->table(1);
        $channelData = SnmpQuery::walk('JUNIPER-DOM-MIB::jnxDomModuleLaneTable')->table(2);

        foreach ($xcData as $ifIndex => $data) {
            $transceiver_id = $transceivers->get($ifIndex)?->id ?? 0;
            if ($transceiver_id === 0) {
                continue; // no transceiver
            }

            // RX Power
            if (isset($channelData[$ifIndex])) {
                // lanes
                foreach ($channelData[$ifIndex] as $laneIndex => $lane) {
                    $metrics->push(new TransceiverMetric([
                        'transceiver_id' => $transceiver_id,
                        'type' => 'power-rx',
                        'oid' => ".1.3.6.1.4.1.2636.3.60.1.2.1.1.6.$ifIndex.$laneIndex",
                        'value' => $lane['JUNIPER-DOM-MIB::jnxDomCurrentLaneRxLaserPower'] / 100,
                        'channel' => $laneIndex,
                        'divisor' => 100,
                        'threshold_min_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerLowAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerLowAlarmThreshold'] / 100 : null,
                        'threshold_min_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerLowWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerLowWarningThreshold'] / 100 : null,
                        'threshold_max_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerHighWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerHighWarningThreshold'] / 100 : null,
                        'threshold_max_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerHighAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerHighAlarmThreshold'] / 100 : null,
                    ]));
                }
            } elseif (isset($data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPower'])) {
                $metrics->push(new TransceiverMetric([
                    'transceiver_id' => $transceiver_id,
                    'type' => 'power-rx',
                    'oid' => ".1.3.6.1.4.1.2636.3.60.1.1.1.1.5.$ifIndex",
                    'value' => $data['JUNIPER-DOM-MIB::jnxDomCurrentLaneTxLaserOutputPower'] / 100,
                    'divisor' => 100,
                    'threshold_min_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerLowAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerLowAlarmThreshold'] / 100 : null,
                    'threshold_min_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerLowWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerLowWarningThreshold'] / 100 : null,
                    'threshold_max_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerHighWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerHighWarningThreshold'] / 100 : null,
                    'threshold_max_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerHighAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentRxLaserPowerHighAlarmThreshold'] / 100 : null,
                ]));
            }

            // TX Power
            if (isset($channelData[$ifIndex])) {
                // lanes
                foreach ($channelData[$ifIndex] as $laneIndex => $lane) {
                    $metrics->push(new TransceiverMetric([
                        'transceiver_id' => $transceiver_id,
                        'type' => 'power-tx',
                        'oid' => ".1.3.6.1.4.1.2636.3.60.1.2.1.1.8.$ifIndex.$laneIndex",
                        'value' => $lane['JUNIPER-DOM-MIB::jnxDomCurrentLaneTxLaserOutputPower'] / 100,
                        'channel' => $laneIndex,
                        'divisor' => 100,
                        'threshold_min_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerLowAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerLowAlarmThreshold'] / 100 : null,
                        'threshold_min_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerLowWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerLowWarningThreshold'] / 100 : null,
                        'threshold_max_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerHighWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerHighWarningThreshold'] / 100 : null,
                        'threshold_max_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerHighAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerHighAlarmThreshold'] / 100 : null,
                    ]));
                }
            } elseif (isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPower'])) {
                $metrics->push(new TransceiverMetric([
                    'transceiver_id' => $transceiver_id,
                    'type' => 'power-tx',
                    'oid' => ".1.3.6.1.4.1.2636.3.60.1.1.1.1.7.$ifIndex",
                    'value' => $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPower'] / 100,
                    'divisor' => 100,
                    'threshold_min_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerLowAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerLowAlarmThreshold'] / 100 : null,
                    'threshold_min_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerLowWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerLowWarningThreshold'] / 100 : null,
                    'threshold_max_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerHighWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerHighWarningThreshold'] / 100 : null,
                    'threshold_max_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerHighAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserOutputPowerHighAlarmThreshold'] / 100 : null,
                ]));
            }

            // Bias Current
            if (isset($channelData[$ifIndex])) {
                // lanes
                foreach ($channelData[$ifIndex] as $laneIndex => $lane) {
                    $metrics->push(new TransceiverMetric([
                        'transceiver_id' => $transceiver_id,
                        'type' => 'bias',
                        'oid' => ".1.3.6.1.4.1.2636.3.60.1.2.1.1.7.$ifIndex.$laneIndex",
                        'value' => $lane['JUNIPER-DOM-MIB::jnxDomCurrentLaneTxLaserBiasCurrent'] / 1000,
                        'channel' => $laneIndex,
                        'divisor' => 1000,
                        'threshold_min_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentLowAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentLowAlarmThreshold'] / 1000 : null,
                        'threshold_min_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentLowWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentLowWarningThreshold'] / 1000 : null,
                        'threshold_max_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentHighWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentHighWarningThreshold'] / 1000 : null,
                        'threshold_max_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentHighAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentHighAlarmThreshold'] / 1000 : null,
                    ]));
                }
            } elseif (isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrent'])) {
                $metrics->push(new TransceiverMetric([
                    'transceiver_id' => $transceiver_id,
                    'type' => 'bias',
                    'oid' => ".1.3.6.1.4.1.2636.3.60.1.1.1.1.6.$ifIndex",
                    'value' => $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrent'] / 1000,
                    'divisor' => 1000,
                    'threshold_min_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentLowAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentLowAlarmThreshold'] / 1000 : null,
                    'threshold_min_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentLowWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentLowWarningThreshold'] / 1000 : null,
                    'threshold_max_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentHighWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentHighWarningThreshold'] / 1000 : null,
                    'threshold_max_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentHighAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentTxLaserBiasCurrentHighAlarmThreshold'] / 1000 : null,
                ]));
            }

            // Temperature
            if (isset($channelData[$ifIndex])) {
                // lanes
                foreach ($channelData[$ifIndex] as $laneIndex => $lane) {
                    $metrics->push(new TransceiverMetric([
                        'transceiver_id' => $transceiver_id,
                        'type' => 'temperature',
                        'oid' => ".1.3.6.1.4.1.2636.3.60.1.2.1.1.9.$ifIndex.$laneIndex",
                        'value' => $lane['JUNIPER-DOM-MIB::jnxDomCurrentLaneLaserTemperature'],
                        'channel' => $laneIndex,
                        'threshold_min_critical' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleTemperatureLowAlarmThreshold'] ?? null,
                        'threshold_min_warning' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleTemperatureLowWarningThreshold'] ?? null,
                        'threshold_max_warning' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleTemperatureHighWarningThreshold'] ?? null,
                        'threshold_max_critical' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleTemperatureHighAlarmThreshold'] ?? null,
                    ]));
                }
            } elseif (isset($data['JUNIPER-DOM-MIB::jnxDomCurrentModuleTemperature'])) {
                $metrics->push(new TransceiverMetric([
                    'transceiver_id' => $transceiver_id,
                    'type' => 'temperature',
                    'oid' => ".1.3.6.1.4.1.2636.3.60.1.1.1.1.8.$ifIndex",
                    'value' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleTemperature'],
                    'threshold_min_critical' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleTemperatureLowAlarmThreshold'] ?? null,
                    'threshold_min_warning' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleTemperatureLowWarningThreshold'] ?? null,
                    'threshold_max_warning' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleTemperatureHighWarningThreshold'] ?? null,
                    'threshold_max_critical' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleTemperatureHighAlarmThreshold'] ?? null,
                ]));
            }

            // Voltage
            if (isset($data['JUNIPER-DOM-MIB::jnxDomCurrentModuleVoltage'])) {
                $metrics->push(new TransceiverMetric([
                    'transceiver_id' => $transceiver_id,
                    'type' => 'voltage',
                    'oid' => ".1.3.6.1.4.1.2636.3.60.1.1.1.1.25.$ifIndex",
                    'value' => $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleVoltage'] / 1000,
                    'divisor' => 1000,
                    'threshold_min_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentModuleVoltageLowAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleVoltageLowAlarmThreshold'] / 1000 : null,
                    'threshold_min_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentModuleVoltageLowWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleVoltageLowWarningThreshold'] / 1000 : null,
                    'threshold_max_warning' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentModuleVoltageHighWarningThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleVoltageHighWarningThreshold'] / 1000 : null,
                    'threshold_max_critical' => isset($data['JUNIPER-DOM-MIB::jnxDomCurrentModuleVoltageHighAlarmThreshold']) ? $data['JUNIPER-DOM-MIB::jnxDomCurrentModuleVoltageHighAlarmThreshold'] / 1000 : null,
                ]));
            }
        }

        return $metrics;
    }

    private function findTransceiverEntityByPortName(array $entPhysical, string $ifName): array
    {
        if (preg_match('#-(\d+/\d+/\d+)#', $ifName, $matches)) {
            $expected_tail = ' @ ' . $matches[1];

            foreach ($entPhysical as $entity) {
                if (isset($entity['ENTITY-MIB::entPhysicalDescr']) && str_ends_with($entity['ENTITY-MIB::entPhysicalDescr'], $expected_tail)) {
                    return $entity;
                }
            }
        }

        return [];
    }
}
