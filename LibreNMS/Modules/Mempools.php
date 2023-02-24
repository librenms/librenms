<?php
/*
 * Mempools.php
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

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Mempool;
use App\Observers\MempoolObserver;
use Illuminate\Support\Collection;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\MempoolsPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;
use Log;

class Mempools implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    public function discover(OS $os): void
    {
        $mempools = $os->discoverMempools()->filter(function (Mempool $mempool) {
            if ($mempool->isValid()) {
                return true;
            }
            Log::debug("Rejecting Mempool $mempool->mempool_index $mempool->mempool_descr: Invalid total value");

            return false;
        });
        $this->calculateAvailable($mempools);

        MempoolObserver::observe(\App\Models\Mempool::class);
        $this->syncModels($os->getDevice(), 'mempools', $mempools);

        echo PHP_EOL;
        $mempools->each(function ($mempool) {
            $this->printMempool($mempool);
        });
    }

    public function poll(OS $os): void
    {
        $mempools = $os->getDevice()->mempools;

        if ($mempools->isEmpty()) {
            return;
        }

        $os instanceof MempoolsPolling
            ? $os->pollMempools($mempools)
            : $this->defaultPolling($os, $mempools);

        $this->calculateAvailable($mempools)->each(function (Mempool $mempool) use ($os) {
            $this->printMempool($mempool);

            if (empty($mempool->mempool_class)) {
                Log::debug('Mempool skipped. Does not include class.');

                return;
            }
            $mempool->save();

            $rrd_def = RrdDefinition::make()
                ->addDataset('used', 'GAUGE', 0)
                ->addDataset('free', 'GAUGE', 0);

            $tags = [
                'mempool_type' => $mempool->mempool_type,
                'mempool_class' => $mempool->mempool_class,
                'mempool_index' => $mempool->mempool_index,
                'rrd_name' => ['mempool', $mempool->mempool_type, $mempool->mempool_class, $mempool->mempool_index],
                'rrd_oldname' => ['mempool', $mempool->mempool_type, $mempool->mempool_index],
                'rrd_def' => $rrd_def,
            ];
            $fields = [
                'used' => $mempool->mempool_used,
                'free' => $mempool->mempool_free,
            ];

            data_update($os->getDeviceArray(), 'mempool', $tags, $fields);
        });
    }

    /**
     * @param  \LibreNMS\OS  $os
     * @param  \Illuminate\Support\Collection  $mempools
     * @return \Illuminate\Support\Collection
     */
    private function defaultPolling($os, $mempools)
    {
        // fetch all data
        $oids = $mempools->map->only(['mempool_perc_oid', 'mempool_used_oid', 'mempool_free_oid', 'mempool_total_oid'])
            ->flatten()->filter()->unique()->values()->all();
        $data = snmp_get_multi_oid($os->getDeviceArray(), $oids);

        $mempools->each(function (Mempool $mempool) use ($data) {
            $mempool->fillUsage(
                $data[$mempool->mempool_used_oid] ?? null,
                $data[$mempool->mempool_total_oid] ?? null,
                $data[$mempool->mempool_free_oid] ?? null,
                $data[$mempool->mempool_perc_oid] ?? null
            );
        });

        return $mempools;
    }

    public function cleanup(Device $device): void
    {
        $device->mempools()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'mempools' => $device->mempools()->orderBy('mempool_type')->orderBy('mempool_id')
                ->get()->map->makeHidden(['device_id', 'mempool_id']),
        ];
    }

    /**
     * Calculate available memory.  This is free + buffers + cached.
     *
     * @param  \Illuminate\Support\Collection  $mempools
     * @return \Illuminate\Support\Collection
     */
    private function calculateAvailable(Collection $mempools)
    {
        if ($mempools->count() > 2) { // optimization
            $system = null;
            $buffers = null;
            $cached = null;

            foreach ($mempools as $mempool) {
                /** @var Mempool $mempool */
                if ($mempool->mempool_class == 'system') {
                    if ($system !== null) {
                        Log::debug('Aborted available calculation, too many system class mempools');

                        return $mempools; // more than one system, abort
                    }
                    $system = $mempool;
                } elseif ($mempool->mempool_class == 'buffers') {
                    if ($buffers !== null) {
                        Log::debug('Aborted available calculation, too many buffers class mempools');

                        return $mempools; // more than one buffer, abort
                    }
                    $buffers = $mempool->mempool_used;
                } elseif ($mempool->mempool_class == 'cached') {
                    if ($cached !== null) {
                        Log::debug('Aborted available calculation, too many cached class mempools');

                        return $mempools; // more than one cache, abort
                    }
                    $cached = $mempool->mempool_used;
                }
            }

            if ($system !== null) {
                $old = Number::formatBi($system->mempool_free);
                $system->fillUsage(($system->mempool_used - $buffers - $cached) / $system->mempool_precision, $system->mempool_total / $system->mempool_precision);
                $new = Number::formatBi($system->mempool_free);
                Log::debug("Free memory adjusted by availability calculation: {$old} -> {$new}\n");
            }
        }

        return $mempools;
    }

    private function printMempool(Mempool $mempool): void
    {
        echo "$mempool->mempool_type [$mempool->mempool_class]: $mempool->mempool_descr: $mempool->mempool_perc%";
        if ($mempool->mempool_total != 100) {
            $used = Number::formatBi($mempool->mempool_used);
            $total = Number::formatBi($mempool->mempool_total);
            echo "  {$used} / {$total}";
        }
        echo PHP_EOL;
    }
}
