<?php
/**
 * Fs-switch.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2019 PipoCanaja
 * @author     PipoCanaja <pipocanaja@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Transceiver;
use App\Models\TransceiverMetric;
use Illuminate\Support\Collection;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\Number;
use SnmpQuery;

class FsSwitch extends OS implements TransceiverDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processors = [];

        // Tests OID from SWITCH MIB.
        $processors_data = snmpwalk_cache_oid($this->getDeviceArray(), 'ssCpuIdle', [], 'SWITCH', 'fs');

        foreach ($processors_data as $index => $entry) {
            $processors[] = Processor::discover(
                'fs-SWITCHMIB',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.27975.1.2.11.' . $index,
                $index,
                'CPU',
                -1,
                100 - $entry['ssCpuIdle']
            );
        }

        return $processors;
    }

    public function discoverTransceivers(): Collection
    {
        $ifIndexToPortId = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');

        return SnmpQuery::cache()->walk('FS-SWITCH-V2-MIB::transbasicinformationTable')->mapTable(function ($data, $ifIndex) use ($ifIndexToPortId) {
            if ($data['FS-SWITCH-V2-MIB::transceiveStatus'] == 'inactive') {
                return null;
            }

            $distance = null;
            $cable = null;
            if (isset($data['FS-SWITCH-V2-MIB::link9SinglemodeLengthKm']) && $data['FS-SWITCH-V2-MIB::link9SinglemodeLengthKm'] != 0) {
                $distance = $data['FS-SWITCH-V2-MIB::link9SinglemodeLengthKm'] * 1000;
                $cable = 'SM';
            } elseif (isset($data['FS-SWITCH-V2-MIB::link9SinglemodeLengthM']) && $data['FS-SWITCH-V2-MIB::link9SinglemodeLengthM'] != 0) {
                $distance = $data['FS-SWITCH-V2-MIB::link9SinglemodeLengthM'];
                $cable = 'SM';
            } elseif (isset($data['FS-SWITCH-V2-MIB::link50MultimodeLength']) && $data['FS-SWITCH-V2-MIB::link50MultimodeLength'] != 0) {
                $distance = $data['FS-SWITCH-V2-MIB::link50MultimodeLength'];
                $cable = 'MM';
            } elseif (isset($data['FS-SWITCH-V2-MIB::link62MultimodeLength']) && $data['FS-SWITCH-V2-MIB::link62MultimodeLength'] != 0) {
                $distance = $data['FS-SWITCH-V2-MIB::link62MultimodeLength'];
                $cable = 'MM';
            } elseif (isset($data['FS-SWITCH-V2-MIB::linkCopperLength']) && $data['FS-SWITCH-V2-MIB::linkCopperLength'] != 0) {
                $distance = $data['FS-SWITCH-V2-MIB::linkCopperLength'];
                $cable = 'Copper';
            }

            return new Transceiver([
                'port_id' => $ifIndexToPortId->get($ifIndex),
                'index' => $ifIndex,
                'vendor' => $data['FS-SWITCH-V2-MIB::transceiveVender'] ?? null,
                'type' => $data['FS-SWITCH-V2-MIB::transceiveType'] ?? null,
                'model' => $data['FS-SWITCH-V2-MIB::transceivePartNumber'] ?? null,
                'serial' => $data['FS-SWITCH-V2-MIB::transceiveSerialNumber'] ?? null,
                'cable' => $cable,
                'distance' => $distance,
                'wavelength' => $data['FS-SWITCH-V2-MIB::transceiveWaveLength'] ?? null,
            ]);
        })->filter();
    }

    public function discoverTransceiverMetrics(Collection $transceivers): Collection
    {
        $metrics = new Collection;

        $data = SnmpQuery::walk('FS-SWITCH-V2-MIB::transReceivePowerTable')->table(1);
        if (empty($data)) {
            return $metrics;
        }
        SnmpQuery::walk('FS-SWITCH-V2-MIB::transTransmitPowerTable')->table(1, $data);
        SnmpQuery::walk('FS-SWITCH-V2-MIB::transTemperinformationTable')->table(1, $data);
        SnmpQuery::walk('FS-SWITCH-V2-MIB::transBiasinformationTable')->table(1, $data);
        SnmpQuery::walk('FS-SWITCH-V2-MIB::transVoltageinformationTable')->table(1, $data);

        foreach ($transceivers as $transceiver) {
            $ifIndex = $transceiver->index;
            $current = $data[$ifIndex];

            // power-rx
            if (! empty($current['FS-SWITCH-V2-MIB::receivepowerCurrent']) && $current['FS-SWITCH-V2-MIB::receivepowerCurrent'] !== '0.00') {
                $values = explode(',', $current['FS-SWITCH-V2-MIB::receivepowerCurrent']);

                foreach ($values as $channel => $value) {
                    $metrics->push(new TransceiverMetric([
                        'transceiver_id' => $transceiver->id,
                        'channel' => $channel,
                        'type' => 'power-rx',
                        'oid' => ".1.3.6.1.4.1.52642.1.37.1.10.6.1.5.$ifIndex",
                        'value' => Number::cast($value),
                        'transform_function' => '\LibreNMS\OS\FsSwitch::parseChannelValue',
                        'threshold_min_critical' => $current['FS-SWITCH-V2-MIB::receivepowerLowAlarmThreshold'] ?? null,
                        'threshold_min_warning' => $current['FS-SWITCH-V2-MIB::receivepowerLowWarnThreshold'] ?? null,
                        'threshold_max_warning' => $current['FS-SWITCH-V2-MIB::receivepowerHighWarnThreshold'] ?? null,
                        'threshold_max_critical' => $current['FS-SWITCH-V2-MIB::receivepowerHighAlarmThreshold'] ?? null,
                    ]));
                }
            }

            // power-tx
            if (! empty($current['FS-SWITCH-V2-MIB::transpowerCurrent']) && $current['FS-SWITCH-V2-MIB::transpowerCurrent'] !== '0.00') {
                foreach (explode(',', $current['FS-SWITCH-V2-MIB::transpowerCurrent']) as $channel => $value) {
                    $metrics->push(new TransceiverMetric([
                        'transceiver_id' => $transceiver->id,
                        'channel' => $channel,
                        'type' => 'power-tx',
                        'oid' => ".1.3.6.1.4.1.52642.1.37.1.10.5.1.5.$ifIndex",
                        'value' => Number::cast($value),
                        'transform_function' => '\LibreNMS\OS\FsSwitch::parseChannelValue',
                        'threshold_min_critical' => $current['FS-SWITCH-V2-MIB::transpowerLowAlarmThreshold'] ?? null,
                        'threshold_min_warning' => $current['FS-SWITCH-V2-MIB::transpowerLowWarnThreshold'] ?? null,
                        'threshold_max_warning' => $current['FS-SWITCH-V2-MIB::transpowerHighWarnThreshold'] ?? null,
                        'threshold_max_critical' => $current['FS-SWITCH-V2-MIB::transpowerHighAlarmThreshold'] ?? null,
                    ]));
                }
            }

            // temperature
            if (! empty($current['FS-SWITCH-V2-MIB::temperCurrent']) && $current['FS-SWITCH-V2-MIB::temperCurrent'] !== '0.00') {
                foreach (explode(',', $current['FS-SWITCH-V2-MIB::temperCurrent']) as $channel => $value) {
                    $metrics->push(new TransceiverMetric([
                        'transceiver_id' => $transceiver->id,
                        'channel' => $channel,
                        'type' => 'temperature',
                        'oid' => ".1.3.6.1.4.1.52642.1.37.1.10.2.1.5.$ifIndex",
                        'value' => Number::cast($value),
                        'transform_function' => '\LibreNMS\OS\FsSwitch::parseChannelValue',
                        'threshold_min_critical' => $current['FS-SWITCH-V2-MIB::temperLowAlarmThreshold'] ?? null,
                        'threshold_min_warning' => $current['FS-SWITCH-V2-MIB::temperLowWarnThreshold'] ?? null,
                        'threshold_max_warning' => $current['FS-SWITCH-V2-MIB::temperHighWarnThreshold'] ?? null,
                        'threshold_max_critical' => $current['FS-SWITCH-V2-MIB::temperHighAlarmThreshold'] ?? null,
                    ]));
                }
            }

            // bias
            if (! empty($current['FS-SWITCH-V2-MIB::biasCurrent']) && $current['FS-SWITCH-V2-MIB::biasCurrent'] !== '0.00') {
                foreach (explode(',', $current['FS-SWITCH-V2-MIB::biasCurrent']) as $channel => $value) {
                    $metrics->push(new TransceiverMetric([
                        'transceiver_id' => $transceiver->id,
                        'channel' => $channel,
                        'type' => 'bias',
                        'oid' => ".1.3.6.1.4.1.52642.1.37.1.10.4.1.5.$ifIndex",
                        'value' => Number::cast($value),
                        'transform_function' => '\LibreNMS\OS\FsSwitch::parseChannelValue',
                        'threshold_min_critical' => $current['FS-SWITCH-V2-MIB::biasLowAlarmThreshold'] ?? null,
                        'threshold_min_warning' => $current['FS-SWITCH-V2-MIB::biasLowWarnThreshold'] ?? null,
                        'threshold_max_warning' => $current['FS-SWITCH-V2-MIB::biasHighWarnThreshold'] ?? null,
                        'threshold_max_critical' => $current['FS-SWITCH-V2-MIB::biasHighAlarmThreshold'] ?? null,
                    ]));
                }
            }

            // voltage
            if (! empty($current['FS-SWITCH-V2-MIB::voltageCurrent']) && $current['FS-SWITCH-V2-MIB::voltageCurrent'] !== '0.00') {
                foreach (explode(',', $current['FS-SWITCH-V2-MIB::voltageCurrent']) as $channel => $value) {
                    $metrics->push(new TransceiverMetric([
                        'transceiver_id' => $transceiver->id,
                        'channel' => $channel,
                        'type' => 'voltage',
                        'oid' => ".1.3.6.1.4.1.52642.1.37.1.10.3.1.5.$ifIndex",
                        'value' => Number::cast($value),
                        'transform_function' => '\LibreNMS\OS\FsSwitch::parseChannelValue',
                        'threshold_min_critical' => $current['FS-SWITCH-V2-MIB::voltageLowAlarmThreshold'] ?? null,
                        'threshold_min_warning' => $current['FS-SWITCH-V2-MIB::voltageLowWarnThreshold'] ?? null,
                        'threshold_max_warning' => $current['FS-SWITCH-V2-MIB::voltageHighWarnThreshold'] ?? null,
                        'threshold_max_critical' => $current['FS-SWITCH-V2-MIB::voltageHighAlarmThreshold'] ?? null,
                    ]));
                }
            }
        }

        return $metrics;
    }

    public static function parseChannelValue(string $value, TransceiverMetric $metric): float
    {
        return Number::cast(explode(',', $value)[$metric->channel] ?? '');
    }
}
