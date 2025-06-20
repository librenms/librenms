<?php

/*
 * UcdDiskio.php
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

use App\Models\Device;
use App\Models\DiskIo;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class UcdDiskio implements Module
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
        $oids = SnmpQuery::hideMib()->walk('UCD-DISKIO-MIB::diskIOEntry')->table(1);
        $ucddisk = new Collection;

        foreach ($oids as $key => $diskData) {
            if (is_array($diskData)) { // invalid snmp response
                if ($this->valid_disk($os, $diskData['diskIODevice']) &&
                    ($diskData['diskIONRead'] > '0' || $diskData['diskIONWritten'] > '0')) {
                    $ucddisk->push(new DiskIo([
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

                    if ($datastore) {
                        $datastore->put($os->getDeviceArray(), 'ucd_diskio', $tags, $fields);
                    }
                } else {
                    Log::info('Skip Disk: ' . $diskData['diskIODevice']);
                }
            }
        }

        ModuleModelObserver::observe(\App\Models\DiskIo::class);
        $this->syncModels($os->getDevice(), 'diskIo', $ucddisk);
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

    private function valid_disk($os, $disk): bool
    {
        foreach (Config::getCombined($os->getDevice()->os, 'bad_disk_regexp') as $bir) {
            if (preg_match($bir . 'i', $disk)) {
                Log::debug('Ignored Disk: ' . $disk . ' (matched: ' . $bir . ')');

                return false;
            }
        }

        return true;
    }
}
