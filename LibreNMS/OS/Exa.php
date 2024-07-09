<?php
/**
 * Exa.php
 *
 * Calix EXA OS
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Transceiver;
use App\Models\TransceiverMetric;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;

class Exa extends OS implements OSDiscovery, TransceiverDiscovery
{
    public function discoverOS(Device $device): void
    {
        $info = snmp_getnext_multi($this->getDeviceArray(), ['e7CardSoftwareVersion', 'e7CardSerialNumber'], '-OQUs', 'E7-Calix-MIB');
        $device->version = $info['e7CardSoftwareVersion'] ?? null;
        $device->serial = $info['e7CardSerialNumber'] ?? null;
        $device->hardware = 'Calix ' . $device->sysDescr;

        $cards = explode("\n", snmp_walk($this->getDeviceArray(), 'e7CardProvType', '-OQv', 'E7-Calix-MIB'));
        $card_count = [];
        foreach ($cards as $card) {
            $card_count[$card] = ($card_count[$card] ?? 0) + 1;
        }
        $device->features = implode(', ', array_map(function ($card) use ($card_count) {
            return ($card_count[$card] > 1 ? $card_count[$card] . 'x ' : '') . $card;
        }, array_keys($card_count)));
    }

    public function discoverTransceivers(): Collection
    {
        $ifIndexToPortId = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');

        return \SnmpQuery::cache()->walk('E7-Calix-MIB::e7OltPonPortTable')->mapTable(function ($data, $shelf, $card, $port) use ($ifIndexToPortId) {
            // we know these are GPON, so we can infer the ifIndex
            $ifIndex = '3' . str_pad($card, 2, '0', STR_PAD_LEFT) . str_pad($port, 2, '0', STR_PAD_LEFT);

            return new Transceiver([
                'port_id' => $ifIndexToPortId->get($ifIndex),
                'index' => "$shelf.$card.$port",
            ]);
        });
    }

    public function discoverTransceiverMetrics(Collection $transceivers): Collection
    {
        $metrics = new Collection;
        $data = \SnmpQuery::cache()->walk('E7-Calix-MIB::e7OltPonPortTable')->table(3);

        foreach ($data as $chassis => $chassisData) {
            foreach ($chassisData as $card => $cardData) {
                foreach ($cardData as $port => $portData) {
                    // Temperature
                    if (isset($portData['E7-Calix-MIB::e7OltPonPortTemperature'])) {
                        $index = "$chassis.$card.$port";
                        $metrics->push(new TransceiverMetric([
                            'transceiver_id' => $transceivers->get($index)->id,
                            'type' => 'temperature',
                            'oid' => ".1.3.6.1.4.1.6321.1.2.2.2.1.6.2.1.5.$index",
                            'value' => $portData['E7-Calix-MIB::e7OltPonPortTemperature'],
                        ]));
                    }

                    // Bias Current
                    if (isset($portData['E7-Calix-MIB::e7OltPonPortTxBias'])) {
                        $index = "$chassis.$card.$port";
                        $metrics->push(new TransceiverMetric([
                            'transceiver_id' => $transceivers->get($index)->id,
                            'type' => 'bias',
                            'oid' => ".1.3.6.1.4.1.6321.1.2.2.2.1.6.2.1.6.$index",
                            'value' => $portData['E7-Calix-MIB::e7OltPonPortTxBias'] / 1000,
                            'divisor' => 1000,
                        ]));
                    }

                    // TX Power
                    if (! empty($portData['E7-Calix-MIB::e7OltPonPortTxPower'])) {
                        $index = "$chassis.$card.$port";
                        $metrics->push(new TransceiverMetric([
                            'transceiver_id' => $transceivers->get($index)->id,
                            'type' => 'power-tx',
                            'oid' => ".1.3.6.1.4.1.6321.1.2.2.2.1.6.2.1.7.$index",
                            'value' => \LibreNMS\Util\Convert::mwToDbm($portData['E7-Calix-MIB::e7OltPonPortTxPower'] / 10000),
                            'divisor' => 10000,
                            'transform_function' => '\LibreNMS\Util\Convert::mwToDbm',
                        ]));
                    }

                    // RX Power
                    if (! empty($portData['E7-Calix-MIB::e7OltPonPortRxPower'])) {
                        $index = "$chassis.$card.$port";
                        $metrics->push(new TransceiverMetric([
                            'transceiver_id' => $transceivers->get($index)->id,
                            'type' => 'power-rx',
                            'oid' => ".1.3.6.1.4.1.6321.1.2.2.2.1.6.2.1.8.$index",
                            'value' => \LibreNMS\Util\Convert::mwToDbm($portData['E7-Calix-MIB::e7OltPonPortRxPower'] / 10000),
                            'divisor' => 10000,
                            'transform_function' => '\LibreNMS\Util\Convert::mwToDbm',
                        ]));
                    }

                    // Voltage
                    if (isset($portData['E7-Calix-MIB::e7OltPonPortVoltage'])) {
                        $index = "$chassis.$card.$port";
                        $metrics->push(new TransceiverMetric([
                            'transceiver_id' => $transceivers->get($index)->id,
                            'type' => 'voltage',
                            'oid' => ".1.3.6.1.4.1.6321.1.2.2.2.1.6.2.1.9.$index",
                            'value' => $portData['E7-Calix-MIB::e7OltPonPortVoltage'] / 1000,
                            'divisor' => 1000,
                        ]));
                    }
                }
            }
        }

        return $metrics;
    }
}
