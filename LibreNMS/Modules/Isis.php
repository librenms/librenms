<?php

/**
 * Isis.php
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
 * @link       http://librenms.org
 *
 * @copyright  2021 Otto Reinikainen
 * @author     Otto Reinikainen <otto@ottorei.fi>
 */

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\IsisAdjacency;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\IsIsDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\IsIsPolling;
use LibreNMS\OS;
use LibreNMS\Traits\RequiresPollingMethod;
use LibreNMS\Util\IP;
use SnmpQuery;

class Isis implements Module
{
    use SyncsModels;
    use RequiresPollingMethod;

    protected $isis_codes = [
        'l1IntermediateSystem' => 'L1',
        'l2IntermediateSystem' => 'L2',
        'l1L2IntermediateSystem' => 'L1L2',
        'unknown' => 'unknown',
    ];

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp];
    }

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param  OS  $os
     */
    public function discover(OS $os): void
    {
        $adjacencies = $os instanceof IsIsDiscovery
            ? $os->discoverIsIs()
            : $this->discoverIsIsMib($os);

        ModuleModelObserver::observe(IsisAdjacency::class);
        $this->syncModels($os->getDevice(), 'isisAdjacencies', $adjacencies);
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param  OS  $os
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $adjacencies = $os->getDevice()->isisAdjacencies;

        if ($adjacencies->isEmpty()) {
            return; // no data to poll
        }

        $updated = $os instanceof IsIsPolling
            ? $os->pollIsIs($adjacencies)
            : $this->pollIsIsMib($adjacencies, $os);

        $updated->each->save();
    }

    public function discoverIsIsMib(OS $os): Collection
    {
        // Check if the device has any ISIS enabled interfaces
        $circuits = SnmpQuery::enumStrings()->walk('ISIS-MIB::isisCirc');
        $adjacencies = new Collection;

        if ($circuits->isValid()) {
            $adjacencies_data = SnmpQuery::enumStrings()->displayHints(false)->walk('ISIS-MIB::isisISAdj')
                ->mapTable(fn (array $data, string $circuit, string $adjacency) => [
                    'circuit' => $circuit,
                    'adjacency' => $adjacency,
                    'data' => $data,
                ])
                ->groupBy(['circuit', 'adjacency'])
                ->map(fn (Collection $rows) => $rows->last()->pluck('data')->collapse()->all());

            $adjacencies = $circuits->mapTable(function (array $circuit_data, string $circuit_id) use ($os, $adjacencies_data): ?IsisAdjacency {
                if (! isset($circuit_data['ISIS-MIB::isisCircIfIndex'])) {
                    return null;
                }

                if ($circuit_data['ISIS-MIB::isisCircPassiveCircuit'] == 'true') {
                    return null; // Do not poll passive interfaces
                }

                $adjacency_data = $adjacencies_data->get($circuit_id, []);

                $attributes = [
                    'device_id' => $os->getDeviceId(),
                    'ifIndex' => $circuit_data['ISIS-MIB::isisCircIfIndex'],
                    'port_id' => PortCache::getIdFromIfIndex($circuit_data['ISIS-MIB::isisCircIfIndex'], $os->getDevice()),
                    'isisCircAdminState' => $circuit_data['ISIS-MIB::isisCircAdminState'] ?? 'down',
                    'isisISAdjState' => $adjacency_data['ISIS-MIB::isisISAdjState'] ?? 'down',
                ];

                if (! empty($adjacency_data)) {
                    $attributes = array_merge($attributes, [
                        'isisISAdjNeighSysType' => Arr::get($this->isis_codes, $adjacency_data['ISIS-MIB::isisISAdjNeighSysType'] ?? 'unknown', 'unknown'),
                        'isisISAdjNeighSysID' => str_replace(' ', '.', trim($adjacency_data['ISIS-MIB::isisISAdjNeighSysID'] ?? '')),
                        'isisISAdjNeighPriority' => $adjacency_data['ISIS-MIB::isisISAdjNeighPriority'] ?? '',
                        'isisISAdjLastUpTime' => $this->parseAdjacencyTime($adjacency_data),
                        'isisISAdjAreaAddress' => str_replace(' ', '.', trim($adjacency_data['ISIS-MIB::isisISAdjAreaAddress'] ?? '')),
                        'isisISAdjIPAddrType' => $adjacency_data['ISIS-MIB::isisISAdjIPAddrType'] ?? '',
                        'isisISAdjIPAddrAddress' => (string) IP::fromHexString($adjacency_data['ISIS-MIB::isisISAdjIPAddrAddress'] ?? null, true),
                    ]);
                }

                return new IsisAdjacency($attributes);
            })->filter()->values();
        }

        return $adjacencies;
    }

    public function pollIsIsMib(Collection $adjacencies, OS $os): Collection
    {
        $data = SnmpQuery::enumStrings()->walk('ISIS-MIB::isisISAdjState')->table(2);

        if (count($data) !== $adjacencies->where('isisISAdjState', 'up')->count()) {
            Log::info('New Adjacencies, running discovery');

            return $this->fillNew($adjacencies, $this->discoverIsIsMib($os));
        }

        SnmpQuery::enumStrings()->walk('ISIS-MIB::isisISAdjLastUpTime')->table(2, $data);

        $adjacencies->each(function (IsisAdjacency $adjacency) use ($data): void {
            $adjacency_data = Arr::last($data[$adjacency->ifIndex] ?? []);
            $adjacency->isisISAdjState = $adjacency_data['ISIS-MIB::isisISAdjState'] ?? $adjacency->isisISAdjState;
            $adjacency->isisISAdjLastUpTime = $this->parseAdjacencyTime($adjacency_data);
        });

        return $adjacencies;
    }

    protected function parseAdjacencyTime(?array $data): int
    {
        return (int) (max($data['ISIS-MIB::isisISAdjLastUpTime'] ?? 1, 1) / 100);
    }

    public function dataExists(Device $device): bool
    {
        return $device->isisAdjacencies()->exists() || $device->components()->where('type', 'ISIS')->exists();
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     */
    public function cleanup(Device $device): int
    {
        // clean up legacy components from old code
        $legacyDeleted = $device->components()->where('type', 'ISIS')->delete();

        return $device->isisAdjacencies()->delete() + $legacyDeleted;
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'isis_adjacencies' => $device->isisAdjacencies()->orderBy('index')
                ->get()->map->makeHidden(['id', 'device_id', 'port_id']),
        ];
    }
}
