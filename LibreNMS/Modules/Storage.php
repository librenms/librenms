<?php
/**
 * Storage.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\StorageDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\StoragePolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

class Storage implements Module
{
    use SyncsModels;

    public function dependencies(): array
    {
        return [];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        if ($os instanceof StorageDiscovery) {
            $data = $os->discoverStorage()->filter->isValid($os->getName());

            ModuleModelObserver::observe(\App\Models\Storage::class);
            $this->syncModels($os->getDevice(), 'storage', $data);

            Log::info('');
            $data->each($this->printStorage(...));
        }
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $storages = $os->getDevice()->storage;

        if ($storages->isEmpty()) {
            return; // nothing to do
        }

        // poll storage
        if ($os instanceof StoragePolling) {
            $os->pollStorage($storages);
        } else {
            $this->defaultPolling($storages);
        }

        foreach ($storages as $storage) {
            $this->printStorage($storage);

            $datastore->put($os->getDeviceArray(), 'storage', [
                'type' => $storage->type,
                'descr' => $storage->storage_descr,
                'rrd_name' => ['storage', $storage->type, $storage->storage_descr],
                'rrd_def' => RrdDefinition::make()
                    ->addDataset('used', 'GAUGE', 0)
                    ->addDataset('free', 'GAUGE', 0),
            ], [
                'used'   => $storage->storage_used,
                'free'   => $storage->storage_free,
            ]);
        }
    }

    private function defaultPolling(Collection $storages): void
    {
        // fetch all data
        $oids = $storages->map->only(['storage_used_oid', 'storage_size_oid', 'storage_free_oid', 'storage_perc_oid'])
            ->flatten()->filter()->unique()->values()->all();
        $data = \SnmpQuery::get($oids)->values();

        $storages->each(function (\App\Models\Storage $storage) use ($data) {
            $storage->fillUsage(
                $data[$storage->storage_used_oid] ?? null,
                $data[$storage->storage_size_oid] ?? null,
                $data[$storage->storage_free_oid] ?? null,
                $data[$storage->storage_perc_oid] ?? null
            );
        });
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): void
    {
        $device->storage()->delete();
    }

    public function dump(Device $device)
    {
        return [
            'storage' => $device->storage()
                ->orderBy('storage_index')->orderBy('storage_type')
                ->get()->map->makeHidden(['device_id', 'storage_id']),
        ];
    }

    private function printStorage(\App\Models\Storage $storage): void
    {
        $message = "$storage->storage_descr: $storage->storage_perc%";
        if ($storage->storage_size != 100) {
            $used = Number::formatBi($storage->storage_used);
            $total = Number::formatBi($storage->storage_size);
            $message .= "  $used / $total";
        }
        Log::info($message);
    }
}
