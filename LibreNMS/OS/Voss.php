<?php

/**
 * Voss.php
 *
 * Extreme VOSS ISIS Adjacencies
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
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\IsisAdjacency;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Discovery\IsIsDiscovery;
use LibreNMS\Interfaces\Polling\IsIsPolling;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Util\IP;
use SnmpQuery;

class Voss extends Shared\Extreme implements IsIsDiscovery, IsIsPolling {
    /**
     * Array of shortened ISIS codes
     *
     * @var array
     */
    protected $isis_codes = [
        'l1IntermediateSystem' => 'L1',
        'l2IntermediateSystem' => 'L2',
        'l1L2IntermediateSystem' => 'L1L2',
    ];

    public function discoverIsIs(): Collection
    {
        // Check if the device has any ISIS enabled interfaces
        $circuits = SnmpQuery::enumStrings()->walk('ISIS-MIB-LEGACY::isisCirc');
        $adjacencies = new Collection;

        if ($circuits->isValid()) {
            $circuits = $circuits->table(1);
            $adjacencies_data = SnmpQuery::enumStrings()->walk('ISIS-MIB-LEGACY::isisISAdj')->table(2);

            foreach ($adjacencies_data as $circuit_index => $adjacency_list) {
                foreach ($adjacency_list as $adjacency_index => $adjacency_data) {
                    if (empty($circuits[$circuit_index]['ISIS-MIB-LEGACY::isisCircIfIndex'])) {
                        continue;
                    }

                    if (($circuits[$circuit_index]['ISIS-MIB-LEGACY::isisCircPassiveCircuit'] ?? 'true') == 'true') {
                        continue; // Do not poll passive interfaces and bad data
                    }

                    $adjacencies->push(new IsisAdjacency([
                        'device_id' => $this->getDeviceId(),
                        'index' => "[$circuit_index][$adjacency_index]",
                        'ifIndex' => $circuits[$circuit_index]['ISIS-MIB-LEGACY::isisCircIfIndex'],
                        'port_id' => PortCache::getIdFromIfIndex($circuits[$circuit_index]['ISIS-MIB-LEGACY::isisCircIfIndex'], $this->getDevice()),
                        'isisCircAdminState' => $circuits[$circuit_index]['ISIS-MIB-LEGACY::isisCircAdminState'] ?? 'down',
                        'isisISAdjState' => $adjacency_data['ISIS-MIB-LEGACY::isisAdjState'] ?? 'down',
                        'isisISAdjNeighSysType' => Arr::get($this->isis_codes, $adjacency_data['ISIS-MIB-LEGACY::isisISAdjNeighSysType'] ?? '', 'unknown'),
                        'isisISAdjNeighSysID' => $this->formatIsIsId($adjacency_data['ISIS-MIB-LEGACY::isisISAdjNeighSysID'] ?? ''),
                        'isisISAdjNeighPriority' => $adjacency_data['ISIS-MIB-LEGACY::isisISAdjNeighPriority'] ?? '',
                        'isisISAdjLastUpTime' => $this->parseAdjacencyTime($adjacency_data['ISIS-MIB-LEGACY::isisISAdjLastUpTime'] ?? 0),
                        'isisISAdjAreaAddress' => implode(',', array_map($this->formatIsIsId(...), $adjacency_data['ISIS-MIB-LEGACY::isisAreaAddress'] ?? [])),
                        'isisISAdjIPAddrType' => implode(',', $adjacency_data['ISIS-MIB-LEGACY::isisISAdjIPAddrType'] ?? []),
                        'isisISAdjIPAddrAddress' => implode(',', array_map(fn ($ip) => (string) IP::fromHexString($ip, true), $adjacency_data['ISIS-MIB-LEGACY::isisISAdjIPAddrAddress'] ?? [])),
                    ]));
                }
            }
        }

        return $adjacencies;
    }

    public function pollIsIs($adjacencies): Collection
    {
        $states = SnmpQuery::enumStrings()->walk('ISIS-MIB-LEGACY::isisISAdjState')->values();
        $up_count = array_count_values($states)['up'] ?? 0;

        if ($up_count !== $adjacencies->count()) {
            Log::info('New Adjacencies, running discovery');

            return $this->fillNew($adjacencies, $this->discoverIsIs());
        }

        $uptime = SnmpQuery::walk('ISIS-MIB-LEGACY::isisISAdjLastUpTime')->values();

        return $adjacencies->each(function ($adjacency) use ($states, $uptime): void {
            $adjacency->isisISAdjState = $states['ISIS-MIB-LEGACY::isisISAdjState' . $adjacency->index] ?? $adjacency->isisISAdjState;
            $adjacency->isisISAdjLastUpTime = $this->parseAdjacencyTime($uptime['ISIS-MIB-LEGACY::isisISAdjLastUpTime' . $adjacency->index] ?? 0);
        });
    }

    /**
     * Converts SNMP time to int in seconds
     *
     * @param  string|int  $uptime
     * @return int
     */
    protected function parseAdjacencyTime($uptime): int
    {
        return (int) round(max($uptime, 1) / 100);
    }

    protected function formatIsIsId(string $raw): string
    {
        return str_replace(' ', '.', trim($raw));
    }
}
