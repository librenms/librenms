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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
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
        $storages = $os->discoverStorage()->filter->isValid($os->getName());

        ModuleModelObserver::observe(\App\Models\Storage::class);
        $saved = $this->syncModels($os->getDevice(), 'storage', $storages);

        // set default storage percent on new devices
        $saved->each(function (\App\Models\Storage $storage) {
            if ($storage->wasRecentlyCreated) {
                $storage->storage_perc_warn = LibrenmsConfig::get('storage_perc_warn');
                $storage->save();
            }
        });

        Log::info('');
        $storages->each($this->printStorage(...));
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
            $storages = $os->pollStorage($storages);
        } else {
            $storages = $this->defaultPolling($storages);
        }

        // save db updates
        $storages->each->save();

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
                'used' => $storage->storage_used,
                'free' => $storage->storage_free,
            ]);
        }
    }

    private function defaultPolling(Collection $storages): Collection
    {
        // fetch all data
        $oids = $storages->map->only(['storage_used_oid', 'storage_size_oid', 'storage_free_oid', 'storage_perc_oid'])
            ->flatten()->filter()->unique()->values()->all();

        if (empty($oids)) {
            Log::debug('No OIDs to poll');

            return $storages;
        }

        $data = \SnmpQuery::numeric()->get($oids)->values();

        return $storages->each(function (\App\Models\Storage $storage) use ($data) {
            $storage->fillUsage(
                $data[$storage->storage_used_oid] ?? null,
                $storage->storage_units ? $storage->storage_size / $storage->storage_units : null,
                $data[$storage->storage_free_oid] ?? null,
                $data[$storage->storage_perc_oid] ?? null,
            );
        });
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->storage()->delete();
    }

    public function dataExists(Device $device): bool
    {
        return $device->storage()->exists();
    }

    public function dump(Device $device, string $type): ?array
    {
        return [
            'storage' => $device->storage()
                ->orderBy('type')->orderBy('storage_index')
                ->get()->map->makeHidden(['device_id', 'storage_id']),
        ];
    }

    private function printStorage(\App\Models\Storage $storage): void
    {
        $storage_type = str_replace(['hrStorage', 'ucd'], '', $storage->storage_type);
        $message = "$storage->storage_descr ($storage_type): $storage->storage_perc%";
        if ($storage->storage_size != 100) {
            $used = Number::formatBi($storage->storage_used);
            $total = Number::formatBi($storage->storage_size);
            $message .= "  $used / $total";
        }
        Log::info($message);
    }
}
