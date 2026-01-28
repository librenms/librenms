<?php

/**
 * MacAccounting.php
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

use App\Facades\PortCache;
use App\Models\Device;
use App\Observers\ModuleModelObserver;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\MacAccountingDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\MacAccountingPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;

class MacAccounting implements Module
{
    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
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
        if ($os instanceof MacAccountingDiscovery) {
            $macs = $os->discoverMacAccounting();

            ModuleModelObserver::observe(\App\Models\MacAccounting::class);

            // add new
            $existing = $os->getDevice()->macAccounting->keyBy->getCompositeKey();

            foreach ($macs as $mac) {
                $key = $mac->getCompositeKey();
                if ($existing->has($key)) {
                    $existing_mac = $existing->get($key);
                    $existing_mac->fill($mac->attributesToArray());
                    $existing_mac->save(); // existing (outputs .)
                    $existing->forget($key);
                    continue;
                }

                $os->getDevice()->macAccounting()->save($mac);
            }

            // remove older than 1 year
            $year_ago = now()->subYear();
            foreach ($existing as $key => $existing_mac) {
                if ($existing_mac->poll_time < $year_ago) {
                    $existing_mac->delete();
                    $existing->forget($key);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if (! $os instanceof MacAccountingPolling) {
            return;
        }

        if ($os->getDevice()->macAccounting->isEmpty()) {
            return;
        }

        $macs = $os->pollMacAccounting($os->getDevice()->macAccounting->keyBy->getCompositeKey());

        ModuleModelObserver::observe(\App\Models\MacAccounting::class);
        $os->getDevice()->macAccounting()->saveMany($macs->each(function (\App\Models\MacAccounting $mac): void {
            $mac->port_id ??= PortCache::getIdFromIfIndex($mac->ifIndex); // ensure port_id is filled (if new)
            $mac->last_polled = time();
        }));

        $rrd_def = RrdDefinition::make()
            ->addDataset('IN', 'COUNTER', 0, 12500000000)
            ->addDataset('OUT', 'COUNTER', 0, 12500000000)
            ->addDataset('PIN', 'COUNTER', 0, 12500000000)
            ->addDataset('POUT', 'COUNTER', 0, 12500000000);

        foreach ($macs as $mac) {
            $tags = [
                'ifIndex' => $mac->ifIndex,
                'mac' => $mac->mac,
                'rrd_name' => ['cip', $mac->ifIndex, $mac->mac],
                'rrd_def' => $rrd_def,
            ];
            $fields = [
                'IN' => $mac->bytes_in,
                'OUT' => $mac->bytes_out,
                'PIN' => $mac->packets_in,
                'POUT' => $mac->packets_out,
            ];

            $datastore->put($os->getDeviceArray(), 'cip', $tags, $fields);
        }
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->macAccounting()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->macAccounting()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'mac_accounting' => $device->macAccounting()
                ->orderBy('ifIndex')->orderBy('mac')
                ->get()->map->makeHidden(['ma_id', 'device_id', 'port_id', 'last_polled']),
        ];
    }
}
