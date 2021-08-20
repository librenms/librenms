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
 * @copyright  2021 Otto Reinikainen
 * @author     Otto Reinikainen <otto@ottorei.fi>
 */

namespace LibreNMS\Modules;

use App\Models\IsisAdjacency;
use Illuminate\Support\Arr;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\OS\Junos;
use LibreNMS\Util\IP;

class Isis implements Module
{
    protected $isis_codes = [
        'l1IntermediateSystem' => 'L1',
        'l2IntermediateSystem' => 'L2',
        'l1L2IntermediateSystem' => 'L1L2',
        'unknown' => 'unknown',
    ];

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param  OS  $os
     */
    public function discover(OS $os)
    {
        // Check if the device has any ISIS enabled interfaces
        $circuits = snmpwalk_cache_oid($os->getDeviceArray(), 'ISIS-MIB::isisCirc', []);
        $valid_ids = [];

        if (! empty($circuits)) {
            $adjacencies = snmpwalk_cache_twopart_oid($os->getDeviceArray(), 'ISIS-MIB::isisISAdj', [], null, null, '-OQUstx');
            $ifIndex_port_id_map = $os->getDevice()->ports()->pluck('port_id', 'ifIndex');

            // No ISIS enabled interfaces -> delete the component
            foreach ($circuits as $circuit_id => $circuit_data) {
                if (! isset($circuit_data['isisCircIfIndex'])) {
                    continue;
                }

                if ($os instanceof Junos && $circuit_id == 16) {
                    continue; // Do not poll loopback interface
                }

                $adjacency_data = Arr::last($adjacencies[$circuit_id] ?? [[]]);

                $adjacency = IsisAdjacency::updateOrCreate([
                    'device_id' => $os->getDeviceId(),
                    'ifIndex' => $circuit_data['isisCircIfIndex'],
                ], [
                    'port_id' => $ifIndex_port_id_map[$circuit_data['isisCircIfIndex']] ?? null,
                    'isisCircAdminState' => $circuit_data['isisCircAdminState'],
                    'isisISAdjState' => $adjacency_data['isisISAdjState'] ?? $circuit_data['isisCircAdminState'],
                    'isisISAdjNeighSysType' => Arr::get($this->isis_codes, $adjacency_data['isisISAdjNeighSysType'] ?? null, 'unknown'),
                    'isisISAdjNeighSysID' => str_replace(' ', '.', trim($adjacency_data['isisISAdjNeighSysID'] ?? '')),
                    'isisISAdjNeighPriority' => $adjacency_data['isisISAdjNeighPriority'],
                    'isisISAdjLastUpTime' => $this->parseAdjacencyTime($adjacency_data),
                    'isisISAdjAreaAddress' => str_replace(' ', '.', trim($adjacency_data['isisISAdjAreaAddress'] ?? '')),
                    'isisISAdjIPAddrType' => $adjacency_data['isisISAdjIPAddrType'] ?? null,
                    'isisISAdjIPAddrAddress' => (string) IP::fromHexstring($adjacency_data['isisISAdjIPAddrAddress'] ?? null, true),
                ]);

                $valid_ids[] = $adjacency->id;
            }
        }

        // Cleanup
        $os->getDevice()->isisAdjacencies()->whereNotIn('id', $valid_ids)->delete();
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param  OS  $os
     */
    public function poll(OS $os)
    {
        $adjacencies = $os->getDevice()->isisAdjacencies;

        if ($adjacencies->isEmpty()) {
            return; // no data to poll
        }

        $data = snmpwalk_cache_twopart_oid($os->getDeviceArray(), 'isisISAdjState', [], 'ISIS-MIB');

        if (count($data) !== $adjacencies->count()) {
            d_echo('New Adjacencies');
            // don't enable, might be a bad heuristic
//            $this->discover($os);
//            return;
        }

        $data = snmpwalk_cache_twopart_oid($os->getDeviceArray(), 'isisISAdjLastUpTime', $data, 'ISIS-MIB', null, '-OQUst');

        $adjacencies->each(function (IsisAdjacency $adjacency) use (&$data) {
            $adjacency_data = Arr::last($data[$adjacency->ifIndex]);
            $adjacency->isisISAdjState = $adjacency_data['isisISAdjState'];
            $adjacency->isisISAdjLastUpTime = $this->parseAdjacencyTime($adjacency_data);
            $adjacency->save();
            unset($data[$adjacency->ifIndex]);
        });
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     *
     * @param  OS  $os
     */
    public function cleanup(OS $os)
    {
        $os->getDevice()->isisAdjacencies()->delete();
    }

    private function parseAdjacencyTime($data): int
    {
        return (int) max($data['isisISAdjLastUpTime'] ?? 100, 1) / 100;
    }
}
