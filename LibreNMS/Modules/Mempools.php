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
use App\Observers\ModuleModelObserver;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\Providers\MempoolsProvider;
use LibreNMS\Polling\Providers\Snmp\SnmpMempools;
use LibreNMS\Polling\Providers\UnixAgent\UnixAgentMempools;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

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

    /**
     * In order of preference, SNMP knows more about the device than the agent, so its mempools are preferred.
     */
    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp, PollingMethodType::UnixAgent];
    }

    public function shouldDiscover(OS $os): bool
    {
        return $this->preferredMethod($os) !== null;
    }

    /**
     * Mempools come from one method: the most preferred enabled method that has mempools for this device.
     * Its mempools replace those of other methods, so a device polled by several methods has no duplicates.
     * Methods after one that is down are not tried, so mempools do not move to another method while it is down.
     * When no method has mempools, the existing ones are kept, so an incomplete agent output does not remove them.
     */
    public function discover(OS $os): void
    {
        foreach ($this->enabledMethods($os->getDevice()) as $type) {
            if (! $os->getMethodResults()->isAvailable($type)) {
                break;
            }

            $mempools = $this->discoverValid($os, $type);
            if ($mempools->isNotEmpty()) {
                // check if linux or similar and calculate available ram
                $this->calculateAvailable($mempools);

                ModuleModelObserver::observe(Mempool::class);
                $this->syncModelsFromMethod($os->getDevice(), 'mempools', $type, $mempools);

                Log::info('');
                $mempools->each($this->printMempool(...));

                return;
            }
        }

        Log::info('No mempools found, keeping existing mempools');
    }

    /**
     * Methods are only checked when the device has mempools from an enabled method.
     */
    public function shouldPoll(OS $os): bool
    {
        $methods = $this->enabledMethods($os->getDevice());

        return $os->getDevice()->mempools->contains(fn (Mempool $mempool): bool => in_array($mempool->polling_method, $methods, true));
    }

    /**
     * Each mempool is polled by the method that discovered it, mempools of unavailable methods are skipped.
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $polled = new EloquentCollection;

        foreach ($os->getDevice()->mempools->groupBy(fn (Mempool $mempool): string => $mempool->polling_method->value) as $type => $mempools) {
            $provider = $this->provider($os, PollingMethodType::from($type));
            if ($provider !== null) {
                $provider->pollMempools($mempools);
                $polled = $polled->merge($mempools);
            }
        }

        $this->calculateAvailable($polled)->each(function (Mempool $mempool) use ($os, $datastore): void {
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

            $datastore->put($os->getDeviceArray(), 'mempool', $tags, $fields);
        });
    }

    /**
     * The valid mempools of a method.
     *
     * @return Collection<int, Mempool>
     */
    private function discoverValid(OS $os, PollingMethodType $type): Collection
    {
        return ($this->provider($os, $type)?->discoverMempools() ?? new Collection)->filter(function (Mempool $mempool) {
            if ($mempool->isValid()) {
                return true;
            }

            $reason = 'Not enough data';
            if ($mempool->mempool_total <= 0) {
                $reason = "Invalid total value $mempool->mempool_total";
            }

            Log::debug("Rejecting Mempool $mempool->mempool_index $mempool->mempool_descr: $reason");
            Log::info('x');

            return false;
        });
    }

    /**
     * The most preferred enabled method, null when it is not available.
     * Does not fall back to other methods, so mempools are kept while the preferred method is down.
     */
    private function preferredMethod(OS $os): ?PollingMethodType
    {
        $type = $this->enabledMethods($os->getDevice())[0] ?? null;

        return $type !== null && $os->getMethodResults()->isAvailable($type) ? $type : null;
    }

    /**
     * @return list<PollingMethodType>
     */
    private function enabledMethods(Device $device): array
    {
        return array_values(array_filter($this->pollingMethods($device), $device->polling()->isEnabled(...)));
    }

    /**
     * The mempools of a method, null when the method is not available or has none for this device.
     * The method is checked the first time it is needed in this poll or discovery.
     */
    private function provider(OS $os, PollingMethodType $type): ?MempoolsProvider
    {
        $result = $os->getMethodResults()->result($type);
        if (! $result?->isSuccess()) {
            return null;
        }

        return match ($type) {
            PollingMethodType::Snmp => app(SnmpMempools::class, ['os' => $os]),
            PollingMethodType::UnixAgent => UnixAgentMempools::fromOutput((string) $result->stat('output')),
            default => null,
        };
    }

    public function dataExists(Device $device): bool
    {
        return $device->mempools()->exists();
    }

    public function cleanup(Device $device): int
    {
        return $device->mempools()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'mempools' => $device->mempools()->orderBy('mempool_type')->orderBy('mempool_id')
                ->get()->map->makeHidden(['device_id', 'mempool_id', 'polling_method']), // test data is all snmp
        ];
    }

    /**
     * Calculate available memory.  This is free + buffers + cached.
     *
     * @param  Collection  $mempools
     * @return Collection
     */
    private function calculateAvailable(Collection $mempools): Collection
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
                $available_used = $system->mempool_used - $buffers - $cached;
                if ($available_used >= 0) {
                    $system->fillUsage($available_used, $system->mempool_total, multiplier: 1);
                    $new = Number::formatBi($system->mempool_free);
                    Log::debug("Free memory adjusted by availability calculation: {$old} -> {$new}\n");
                }
            }
        }

        return $mempools;
    }

    private function printMempool(Mempool $mempool): void
    {
        $status = "$mempool->mempool_descr ($mempool->mempool_class): $mempool->mempool_perc%";
        if ($mempool->mempool_total != 100) {
            $used = Number::formatBi($mempool->mempool_used);
            $total = Number::formatBi($mempool->mempool_total);
            $status .= "  $used / $total";
        }
        Log::info($status);
    }
}
