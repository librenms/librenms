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

use App\Models\Device;
use App\Models\IsisAdjacency;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\IsIsDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\IsIsPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\IP;

class Isis implements Module
{
    use SyncsModels;

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

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param  \LibreNMS\OS  $os
     */
    public function discover(OS $os): void
    {
        $adjacencies = $os instanceof IsIsDiscovery
            ? $os->discoverIsIs()
            : $this->discoverIsIsMib($os);

        ModuleModelObserver::observe(\App\Models\IsisAdjacency::class);
        $this->syncModels($os->getDevice(), 'isisAdjacencies', $adjacencies);
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param  \LibreNMS\OS  $os
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $adjacencies = $os->getDevice()->isisAdjacencies;

        if (empty($adjacencies)) {
            return; // no data to poll
        }

        $updated = $os instanceof IsIsPolling
            ? $os->pollIsIs($adjacencies)
            : $this->pollIsIsMib($adjacencies, $os);

        $updated->each->save();
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     */
    public function cleanup(Device $device): void
    {
        $device->isisAdjacencies()->delete();

        // clean up legacy components from old code
        $device->components()->where('type', 'ISIS')->delete();
    }

    public function discoverIsIsMib(OS $os): Collection
    {
        // Check if the device has any ISIS enabled interfaces
        $circuits = snmpwalk_cache_oid($os->getDeviceArray(), 'ISIS-MIB::isisCirc', []);
        $adjacencies = new Collection;

        if (! empty($circuits)) {
            $adjacencies_data = snmpwalk_cache_twopart_oid($os->getDeviceArray(), 'ISIS-MIB::isisISAdj', [], null, null, '-OQUstx');
            $ifIndex_port_id_map = $os->getDevice()->ports()->pluck('port_id', 'ifIndex');

            // No ISIS enabled interfaces -> delete the component
            foreach ($circuits as $circuit_id => $circuit_data) {
                if (! isset($circuit_data['isisCircIfIndex'])) {
                    continue;
                }

                if ($circuit_data['isisCircPassiveCircuit'] == 'true') {
                    continue; // Do not poll passive interfaces
                }

                $adjacency_data = Arr::last($adjacencies_data[$circuit_id] ?? [[]]);

                $attributes = [
                    'device_id' => $os->getDeviceId(),
                    'ifIndex' => $circuit_data['isisCircIfIndex'],
                    'port_id' => $ifIndex_port_id_map[$circuit_data['isisCircIfIndex']] ?? null,
                    'isisCircAdminState' => $circuit_data['isisCircAdminState'] ?? 'down',
                    'isisISAdjState' => $adjacency_data['isisISAdjState'] ?? 'down',
                ];

                if (! empty($adjacency_data)) {
                    $attributes = array_merge($attributes, [
                        'isisISAdjNeighSysType' => Arr::get($this->isis_codes, $adjacency_data['isisISAdjNeighSysType'] ?? 'unknown', 'unknown'),
                        'isisISAdjNeighSysID' => str_replace(' ', '.', trim($adjacency_data['isisISAdjNeighSysID'] ?? '')),
                        'isisISAdjNeighPriority' => $adjacency_data['isisISAdjNeighPriority'] ?? '',
                        'isisISAdjLastUpTime' => $this->parseAdjacencyTime($adjacency_data),
                        'isisISAdjAreaAddress' => str_replace(' ', '.', trim($adjacency_data['isisISAdjAreaAddress'] ?? '')),
                        'isisISAdjIPAddrType' => $adjacency_data['isisISAdjIPAddrType'] ?? '',
                        'isisISAdjIPAddrAddress' => (string) IP::fromHexString($adjacency_data['isisISAdjIPAddrAddress'] ?? null, true),
                    ]);
                }

                $adjacencies->push(new IsisAdjacency($attributes));
            }
        }

        return $adjacencies;
    }

    public function pollIsIsMib(Collection $adjacencies, OS $os): Collection
    {
        $data = snmpwalk_cache_twopart_oid($os->getDeviceArray(), 'isisISAdjState', [], 'ISIS-MIB');

        if (count($data) !== $adjacencies->where('isisISAdjState', 'up')->count()) {
            echo 'New Adjacencies, running discovery';

            // don't enable, might be a bad heuristic
            return $this->fillNew($adjacencies, $this->discoverIsIsMib($os));
        }

        $data = snmpwalk_cache_twopart_oid($os->getDeviceArray(), 'isisISAdjLastUpTime', $data, 'ISIS-MIB', null, '-OQUst');

        $adjacencies->each(function (IsisAdjacency $adjacency) use (&$data) {
            $adjacency_data = Arr::last($data[$adjacency->ifIndex] ?? []);
            $adjacency->isisISAdjState = $adjacency_data['isisISAdjState'] ?? $adjacency->isisISAdjState;
            $adjacency->isisISAdjLastUpTime = $this->parseAdjacencyTime($adjacency_data);
            $adjacency->save();
            unset($data[$adjacency->ifIndex]);
        });

        return $adjacencies;
    }

    protected function parseAdjacencyTime($data): int
    {
        return (int) (max($data['isisISAdjLastUpTime'] ?? 1, 1) / 100);
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'isis_adjacencies' => $device->isisAdjacencies()->orderBy('index')
                ->get()->map->makeHidden(['id', 'device_id', 'port_id']),
        ];
    }
}
