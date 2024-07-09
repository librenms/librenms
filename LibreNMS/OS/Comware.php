<?php
/**
 * Comware.php
 *
 * H3C/HPE Comware OS
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Mempool;
use App\Models\Transceiver;
use App\Models\TransceiverMetric;
use Illuminate\Support\Collection;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\Convert;

class Comware extends OS implements MempoolsDiscovery, ProcessorDiscovery, TransceiverDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        // serial
        $serial_nums = explode("\n", snmp_walk($this->getDeviceArray(), 'hh3cEntityExtManuSerialNum', '-Osqv', 'HH3C-ENTITY-EXT-MIB'));
        $this->getDevice()->serial = $serial_nums[0]; // use the first s/n
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processors = [];
        $procdata = snmpwalk_group($this->getDeviceArray(), 'hh3cEntityExtCpuUsage', 'HH3C-ENTITY-EXT-MIB');

        if (empty($procdata)) {
            return $processors;
        }
        $entity_data = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');

        foreach ($procdata as $index => $usage) {
            if ($usage['hh3cEntityExtCpuUsage'] != 0) {
                $processors[] = Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    ".1.3.6.1.4.1.25506.2.6.1.1.1.1.6.$index",
                    $index,
                    $entity_data[$index],
                    1,
                    $usage['hh3cEntityExtCpuUsage'],
                    null,
                    $index
                );
            }
        }

        return $processors;
    }

    public function discoverMempools()
    {
        $mempools = new Collection();
        $data = snmpwalk_group($this->getDeviceArray(), 'hh3cEntityExtMemUsage', 'HH3C-ENTITY-EXT-MIB');

        if (empty($data)) {
            return $mempools; // avoid additional walks
        }

        $data = snmpwalk_group($this->getDeviceArray(), 'hh3cEntityExtMemSize', 'HH3C-ENTITY-EXT-MIB', 1, $data);
        $entity_name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $entity_class = $this->getCacheByIndex('entPhysicalClass', 'ENTITY-MIB');

        foreach ($data as $index => $entry) {
            if ($entity_class[$index] == 'module' && $entry['hh3cEntityExtMemUsage'] > 0) {
                $mempools->push((new Mempool([
                    'mempool_index' => $index,
                    'mempool_type' => 'comware',
                    'mempool_class' => 'system',
                    'mempool_descr' => $entity_name[$index],
                    'mempool_precision' => 1,
                    'mempool_perc_oid' => ".1.3.6.1.4.1.25506.2.6.1.1.1.1.8.$index",
                ]))->fillUsage(null, $entry['hh3cEntityExtMemSize'] ?? null, null, $entry['hh3cEntityExtMemUsage'] ?? null));
            }
        }

        return $mempools;
    }

    public function discoverTransceivers(): Collection
    {
        $ifIndexToPortId = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');

        return \SnmpQuery::enumStrings()->cache()->walk('HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverInfoTable')->mapTable(function ($data, $ifIndex) use ($ifIndexToPortId) {
            return new Transceiver([
                'port_id' => $ifIndexToPortId->get($ifIndex, 0),
                'index' => $ifIndex,
                'type' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverType'] ?? null,
                'vendor' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverVendorName'] ?? null,
                'oui' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverVendorOUI'] ?? null,
                'revision' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverRevisionNumber'] ?? null,
                'model' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverPartNumber'] ?? null,
                'serial' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverSerialNumber'] ?? null,
                'ddm' => isset($data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverDiagnostic']) && $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverDiagnostic'] == 'true',
                'cable' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverHardwareType'] ?? null,
                'distance' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverTransferDistance'] ?? null,
                'wavelength' => isset($data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverWaveLength']) && $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverWaveLength'] != 2147483647 ? $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverWaveLength'] : null,
            ]);
        });
    }

    public function discoverTransceiverMetrics(Collection $transceivers): Collection
    {
        $metrics = new Collection;

        $xcData = \SnmpQuery::enumStrings()->cache()->walk('HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverInfoTable')->table(1);
        $channelData = \SnmpQuery::walk('HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelTable')->table(2);

        foreach ($xcData as $ifIndex => $transceiver) {
            $transceiver_id = $transceivers->get($ifIndex)->id;
            // TX Power
            if (isset($transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurTXPower']) && $transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurTXPower'] != 2147483647) {
                $metrics->push($this->setTransceiverThresholds(new TransceiverMetric([
                    'transceiver_id' => $transceiver_id,
                    'type' => 'power-tx',
                    'oid' => ".1.3.6.1.4.1.25506.2.70.1.1.1.9.$ifIndex",
                    'value' => $transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurTXPower'] / 100, // dBm
                    'divisor' => 100,
                ]), $transceiver, 'PwrOut', fn ($v) => Convert::uwToDbm($v / 10)));
            }

            // RX Power
            if (isset($transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurRXPower']) && $transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurRXPower'] != 2147483647) {
                $metrics->push($this->setTransceiverThresholds(new TransceiverMetric([
                    'transceiver_id' => $transceiver_id,
                    'type' => 'power-rx',
                    'oid' => ".1.3.6.1.4.1.25506.2.70.1.1.1.12.$ifIndex",
                    'value' => $transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurRXPower'] / 100,
                    'divisor' => 100,
                ]), $transceiver, 'RcvPwr', fn ($v) => Convert::uwToDbm($v / 10)));
            }

            // Temperature
            if (isset($transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverTemperature']) && $transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverTemperature'] != 2147483647) {
                $metrics->push($this->setTransceiverThresholds(new TransceiverMetric([
                    'transceiver_id' => $transceiver_id,
                    'type' => 'temperature',
                    'oid' => ".1.3.6.1.4.1.25506.2.70.1.1.1.15.$ifIndex",
                    'value' => $transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverTemperature'],
                ]), $transceiver, 'Temp', fn ($v) => $v / 1000));
            }

            // Bias Current
            if (isset($transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverBiasCurrent']) && $transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverBiasCurrent'] != 2147483647) {
                $metrics->push($this->setTransceiverThresholds(new TransceiverMetric([
                    'transceiver_id' => $transceiver_id,
                    'type' => 'bias',
                    'oid' => ".1.3.6.1.4.1.25506.2.70.1.1.1.17.$ifIndex",
                    'value' => $transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverBiasCurrent'] / 100,
                    'divisor' => 100,
                ]), $transceiver, 'Bias', fn ($v) => $v / 1000));
            }

            // Voltage
            if (isset($transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverVoltage']) && $transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverVoltage'] != 2147483647) {
                $metrics->push($this->setTransceiverThresholds(new TransceiverMetric([
                    'transceiver_id' => $transceiver_id,
                    'type' => 'voltage',
                    'oid' => ".1.3.6.1.4.1.25506.2.70.1.1.1.16.$ifIndex",
                    'value' => $transceiver['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverVoltage'] / 100,
                    'divisor' => 100,
                ]), $transceiver, 'Vcc', fn ($v) => $v / 10000));
            }

            // Channels

            if (! empty($channelData[$ifIndex])) {
                foreach ($channelData[$ifIndex] as $channel => $channelDatum) {
                    // Temperature
                    if (isset($channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelTemperature']) && $channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelTemperature'] != 2147483647) {
                        $metrics->push($this->setTransceiverThresholds(new TransceiverMetric([
                            'transceiver_id' => $transceiver_id,
                            'channel' => $channel,
                            'type' => 'temperature',
                            'oid' => ".1.3.6.1.4.1.25506.2.70.1.2.1.4.$ifIndex.$channel",
                            'value' => $channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelTemperature'],
                        ]), $transceiver, 'Temp', fn ($v) => $v / 1000));
                    }

                    // TX Power
                    if (isset($channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelCurTXPower']) && $channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelCurTXPower'] != 2147483647) {
                        $metrics->push($this->setTransceiverThresholds(new TransceiverMetric([
                            'transceiver_id' => $transceiver_id,
                            'channel' => $channel,
                            'type' => 'power-tx',
                            'oid' => ".1.3.6.1.4.1.25506.2.70.1.2.1.2.$ifIndex.$channel",
                            'value' => $channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelCurTXPower'] / 100,
                            'divisor' => 100,
                        ]), $transceiver, 'PwrOut', fn ($v) => Convert::uwToDbm($v / 10)));
                    }

                    // RX Power
                    if (isset($channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelCurRXPower']) && $channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelCurRXPower'] != 2147483647) {
                        $metrics->push($this->setTransceiverThresholds(new TransceiverMetric([
                            'transceiver_id' => $transceiver_id,
                            'channel' => $channel,
                            'type' => 'power-tx',
                            'oid' => ".1.3.6.1.4.1.25506.2.70.1.2.1.3.$ifIndex.$channel",
                            'value' => $channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelCurTXPower'] / 100,
                            'divisor' => 100,
                        ]), $transceiver, 'RcvPwr', fn ($v) => Convert::uwToDbm($v / 10)));
                    }

                    // Bias Current
                    if (isset($channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelBiasCurrent']) && $channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelBiasCurrent'] != 2147483647) {
                        $metrics->push($this->setTransceiverThresholds(new TransceiverMetric([
                            'transceiver_id' => $transceiver_id,
                            'channel' => $channel,
                            'type' => 'bias',
                            'oid' => ".1.3.6.1.4.1.25506.2.70.1.2.1.5.$ifIndex.$channel",
                            'value' => $channelDatum['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverChannelBiasCurrent'] / 100,
                            'divisor' => 100,
                        ]), $transceiver, 'Bias', fn ($v) => $v / 1000));
                    }
                }
            }
        }

        return $metrics;
    }

    private function setTransceiverThresholds(TransceiverMetric $metric, array $data, string $slug, callable $transform = null): TransceiverMetric
    {
        $transform ??= fn ($v) => $v; // default do nothing transform
        $metric->threshold_min_critical = ! empty($data["HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiver{$slug}LoAlarm"]) ? $transform($data["HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiver{$slug}LoAlarm"]) : null;
        $metric->threshold_min_warning = ! empty($data["HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiver{$slug}LoWarn"]) ? $transform($data["HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiver{$slug}LoWarn"]) : null;
        $metric->threshold_max_warning = ! empty($data["HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiver{$slug}HiWarn"]) ? $transform($data["HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiver{$slug}HiWarn"]) : null;
        $metric->threshold_max_critical = ! empty($data["HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiver{$slug}HiAlarm"]) ? $transform($data["HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiver{$slug}HiAlarm"]) : null;

        return $metric;
    }
}
