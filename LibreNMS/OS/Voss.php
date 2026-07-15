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
use SnmpQuery;

class Voss extends Shared\Extreme implements IsIsDiscovery, IsIsPolling
{
    use SyncsModels;
    /**
     * Array of shortened ISIS codes
     *
     * @var array
     */
    protected array $isis_codes = [
        'l1IntermediateSystem' => 'L1',
        'l2IntermediateSystem' => 'L2',
        'l1L2IntermediateSystem' => 'L1L2',
    ];

     /**
     * @return Collection<int, IsisAdjacency>
     */
    public function discoverIsIs(): Collection
    {
        // Check if the device has any ISIS enabled interfaces
        $circuits = SnmpQuery::enumStrings()->walk('ISIS-MIB-LEGACY::isisCirc');
        $adjacencies = new Collection;

        if ($circuits->isValid()) {
            $circuits = $circuits->table(1);
            $adjacencies_data = SnmpQuery::enumStrings()->walk('ISIS-MIB-LEGACY::isisISAdj')->table(2);
            $area_addr = SnmpQuery::walk('ISIS-MIB-LEGACY::isisAreaAddr')->values();
            $device_area = reset($area_addr);
            $device_area = str_replace(' ', '', reset($area_addr));
            $device_area = substr($device_area, 0, 2) . '.' . substr($device_area, 2);
            $lsp_tlvs = SnmpQuery::numeric()
                ->numericIndex()
                ->walk('ISIS-MIB-LEGACY::isisLSPTLVValue')
                ->values();
            $isis_hostnames = $this->parseIsisLspHostnames($lsp_tlvs);
            foreach ($adjacencies_data as $circuit_index => $adjacency_list) {
                foreach ($adjacency_list as $adjacency_index => $adjacency_data) {
                    if (empty($circuits[$circuit_index]['ISIS-MIB-LEGACY::isisCircIfIndex'])) {
                        continue;
                    }

                    if (($circuits[$circuit_index]['ISIS-MIB-LEGACY::isisCircPassiveCircuit'] ?? 'true') == 'true') {
                        continue; // Do not poll passive interfaces and bad data
                    }
                    $neigh_sys_id = $this->formatIsIsId(
                        $adjacency_data['ISIS-MIB-LEGACY::isisISAdjNeighSysID'] ?? ''
                    );
                    $neigh_name = $isis_hostnames[$neigh_sys_id] ?? '';
                    $adjacencies->push(new IsisAdjacency([
                        'device_id' => $this->getDeviceId(),
                        'index' => "[$circuit_index][$adjacency_index]",
                        'ifIndex' => $circuits[$circuit_index]['ISIS-MIB-LEGACY::isisCircIfIndex'],
                        'port_id' => PortCache::getIdFromIfIndex($circuits[$circuit_index]['ISIS-MIB-LEGACY::isisCircIfIndex'], $this->getDevice()),
                        'isisCircAdminState' => $circuits[$circuit_index]['ISIS-MIB-LEGACY::isisCircAdminState'] ?? 'down',
                        'isisISAdjState' => $adjacency_data['ISIS-MIB-LEGACY::isisISAdjState'] ?? 'down',
                        'isisISAdjNeighSysType' => Arr::get($this->isis_codes, $adjacency_data['ISIS-MIB-LEGACY::isisISAdjNeighSysType'] ?? '', 'unknown'),
                        'isisISAdjNeighSysID' => $this->formatIsIsId($adjacency_data['ISIS-MIB-LEGACY::isisISAdjNeighSysID'] ?? ''),
                        'isisISAdjNeighPriority' => $adjacency_data['ISIS-MIB-LEGACY::isisISAdjNeighPriority'] ?? '',
                        'isisISAdjLastUpTime' => $this->parseAdjacencyTime($adjacency_data['ISIS-MIB-LEGACY::isisISAdjLastUpTime'] ?? 0),
                        'isisISAdjIPAddrType' => $neigh_name ? 'hostname' : '',
                        'isisISAdjIPAddrAddress' => $neigh_name,
                        'isisISAdjAreaAddress' => $device_area,
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

     /**
     * Need to get hostnames for adjacent neighbor from isisLSPTLVValue as they aren't exposed elsewhere
     *
     * @param array<string, mixed> $tlvs
     * @return array<string, string>
     */
    protected function parseIsisLspHostnames(array $tlvs): array
    {
        $hostnames = [];
        foreach ($tlvs as $oid => $value) {
            $value = trim((string) $value, " \t\n\r\0\x0B\"");
            if ($value === '') {
                continue;
            }
            if (preg_match('/^[0-9A-Fa-f]{2}( [0-9A-Fa-f]{2})+$/', $value)) {
                continue;
            }
            if (! preg_match('/^[A-Za-z0-9_.:-]+$/', $value)) {
                continue;
            }
            preg_match_all('/\d+/', (string) $oid, $matches);
            $numbers = array_map(intval(...), $matches[0]);
            if (count($numbers) < 10) {
                continue;
            }

            $index = array_slice($numbers, -10);
            $sys_id_bytes = array_slice($index, 1, 6);
            $sys_id = implode('.', array_map(
                fn ($byte) => strtoupper(str_pad(dechex($byte), 2, '0', STR_PAD_LEFT)),
                $sys_id_bytes
            ));

            $hostnames[$sys_id] = $value;
        }

        return $hostnames;
    }
}
