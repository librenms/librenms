<?php

/*
 * UcdMib.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2025 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\Modules;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DiskIo;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class UcdMib implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $this->poll($os);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, ?DataStorageInterface $datastore = null): void
    {
        $ucdDisk = $this->ucdDisk($os, $datastore);

        ModuleModelObserver::observe(\App\Models\DiskIo::class);
        $this->syncModels($os->getDevice(), 'diskIo', $ucdDisk);

        $this->ucdCpu($os, $datastore);
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->diskIo()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->diskIo()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'disks' => $device->diskIo()
                ->orderBy('diskio_descr')
                ->get()->map->makeHidden(['diskio_id', 'device_id']),
        ];
    }

    public function ucdCpu($os, $datastore)
    {
        $oids = SnmpQuery::hideMib()->walk('UCD-SNMP-MIB::systemStats')->table(1);
        $oids = (! empty($oids[0])) ? $oids[0] : [];

        if (is_numeric($oids['ssCpuRawUser'] ?? null)
            && is_numeric($oids['ssCpuRawNice'] ?? null)
            && is_numeric($oids['ssCpuRawSystem'] ?? null)
            && is_numeric($oids['ssCpuRawIdle'] ?? null)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('user', 'COUNTER', 0)
                ->addDataset('system', 'COUNTER', 0)
                ->addDataset('nice', 'COUNTER', 0)
                ->addDataset('idle', 'COUNTER', 0);

            $fields = [
                'user' => $oids['ssCpuRawUser'],
                'system' => $oids['ssCpuRawSystem'],
                'nice' => $oids['ssCpuRawNice'],
                'idle' => $oids['ssCpuRawIdle'],
            ];

            $tags = ['rrd_def' => $rrd_def];
            $os->enableGraph('ucd_cpu');

            if ($datastore) { // discovery or polling
                $datastore->put($os->getDeviceArray(), 'ucd_cpu', $tags, $fields);
            }
        }

        $collectData = [
            'ssCpuRawUser' => null,
            'ssCpuRawNice' => null,
            'ssCpuRawSystem' => null,
            'ssCpuRawIdle' => null,
            'ssCpuRawInterrupt' => null,
            'ssCpuRawSoftIRQ' => null,
            'ssCpuRawKernel' => null,
            'ssCpuRawWait' => 'ucd_io_wait',
            'ssIORawSent' => 'ucd_io',
            'ssIORawReceived' => null,
            'ssRawInterrupts' => 'ucd_interrupts',
            'ssRawContexts' => 'ucd_contexts',
            'ssRawSwapIn' => 'ucd_swap_io',
            'ssRawSwapOut' => null,
            'ssCpuRawSteal' => 'ucd_cpu_steal',
        ];

        foreach ($collectData as $key => $graph) {
            if (is_numeric($oids[$key] ?? null)) {
                $rrd_name = 'ucd_' . $key;
                $rrd_def = RrdDefinition::make()->addDataset('value', 'COUNTER', 0);

                $fields = [
                    'value' => $oids[$key],
                ];

                $tags = ['oid' => $key, 'rrd_name' => $rrd_name, 'rrd_def' => $rrd_def];
                $os->enableGraph('ucd_cpu');
                if (! empty($graph)) {
                    $os->enableGraph($graph);
                }

                if ($datastore) { // discovery or polling
                    $datastore->put($os->getDeviceArray(), 'ucd_cpu', $tags, $fields);
                }
            }
        }

        $oids = SnmpQuery::hideMib()->walk('UCD-SNMP-MIB::laLoadInt')->table(1);

        if (! empty($oids)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('1min', 'GAUGE', 0)
                ->addDataset('5min', 'GAUGE', 0)
                ->addDataset('15min', 'GAUGE', 0);

            $fields = [
                '1min' => $oids[1]['laLoadInt'] ?? null,
                '5min' => $oids[2]['laLoadInt'] ?? null,
                '15min' => $oids[3]['laLoadInt'] ?? null,
            ];

            $tags = ['rrd_def' => $rrd_def];
            $os->enableGraph('ucd_load');

            if ($datastore) { // discovery or polling
                $datastore->put($os->getDeviceArray(), 'ucd_load', $tags, $fields);
            }
        }
    }

    public function ucdDisk($os, $datastore): Collection
    {
        $oids = SnmpQuery::hideMib()->walk('UCD-DISKIO-MIB::diskIOTable')->table(1);
        $ucdDisk = new Collection;

        foreach ($oids as $key => $diskData) {
            if (is_array($diskData)) { // invalid snmp response
                if ($this->valid_disk($os, $diskData['diskIODevice']) &&
                    ($diskData['diskIONRead'] > '0' || $diskData['diskIONWritten'] > '0')) {
                    $ucdDisk->push(new DiskIo([
                        'diskio_index' => $diskData['diskIOIndex'],
                        'diskio_descr' => $diskData['diskIODevice'],
                    ]));

                    $tags = [
                        'rrd_name' => ['ucd_diskio', $diskData['diskIODevice']],
                        'rrd_def' => RrdDefinition::make()
                            ->addDataset('read', 'DERIVE', 0, 125000000000)
                            ->addDataset('written', 'DERIVE', 0, 125000000000)
                            ->addDataset('reads', 'DERIVE', 0, 125000000000)
                            ->addDataset('writes', 'DERIVE', 0, 125000000000),
                        'descr' => $diskData['diskIODevice'],
                    ];

                    $fields = [
                        'read' => $diskData['diskIONReadX'],
                        'written' => $diskData['diskIONWrittenX'],
                        'reads' => $diskData['diskIOReads'],
                        'writes' => $diskData['diskIOWrites'],
                    ];

                    if ($datastore) { // discovery or polling
                        $datastore->put($os->getDeviceArray(), 'ucd_diskio', $tags, $fields);
                    }
                } else {
                    Log::info('Skip Disk: ' . $diskData['diskIODevice']);
                }
            }
        }

        return $ucdDisk;
    }

    private function valid_disk($os, $disk): bool
    {
        foreach (LibrenmsConfig::getCombined($os->getDevice()->os, 'bad_disk_regexp') as $bir) {
            if (preg_match($bir . 'i', $disk)) {
                Log::debug('Ignored Disk: ' . $disk . ' (matched: ' . $bir . ')');

                return false;
            }
        }

        return true;
    }
}
