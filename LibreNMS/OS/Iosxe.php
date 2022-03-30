<?php
/**
 * Iosxe.php
 *
 * Cisco IOS-XE Wireless LAN Controller
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\IsisAdjacency;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Discovery\IsIsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCellDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessChannelDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\IsIsPolling;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\OS\Traits\CiscoCellular;
use LibreNMS\Util\IP;

class Iosxe extends OS implements
    IsIsDiscovery,
    IsIsPolling,
    OSPolling,
    WirelessCellDiscovery,
    WirelessChannelDiscovery,
    WirelessRssiDiscovery,
    WirelessRsrqDiscovery,
    WirelessRsrpDiscovery,
    WirelessSnrDiscovery
{
    use SyncsModels;
    use CiscoCellular;

    public function pollOS(): void
    {
        // Don't poll Ciscowlc FIXME remove when wireless-controller module exists
    }

    protected $isis_codes = [
        'l1IntermediateSystem' => 'L1',
        'l2IntermediateSystem' => 'L2',
        'l1L2IntermediateSystem' => 'L1L2',
        'unknown' => 'unknown',
    ];

    public function discoverIsIs(): Collection
    {
        // Check if the device has any ISIS enabled interfaces
        $circuits = snmpwalk_cache_oid($this->getDeviceArray(), 'CISCO-IETF-ISIS-MIB::ciiCirc', []);
        $adjacencies = new Collection;

        if (! empty($circuits)) {
            $adjacencies_data = snmpwalk_cache_twopart_oid($this->getDeviceArray(), 'CISCO-IETF-ISIS-MIB::ciiISAdj', [], null, null, '-OQUstx');
            $ifIndex_port_id_map = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');

            // No ISIS enabled interfaces -> delete the component
            foreach ($circuits as $circuit_id => $circuit_data) {
                if (! isset($circuit_data['ciiCircIfIndex'])) {
                    continue;
                }

                if ($circuit_data['ciiCircPassiveCircuit'] == 'true') {
                    continue; // Do not poll passive interfaces
                }

                $adjacency_data = Arr::last($adjacencies_data[$circuit_id] ?? [[]]);

                $attributes = [
                    'device_id' => $this->getDeviceId(),
                    'ifIndex' => $circuit_data['ciiCircIfIndex'],
                    'port_id' => $this->ifIndexToId($circuit_data['ciiCircIfIndex']) ?? null,
                    'isisCircAdminState' => $circuit_data['ciiCircAdminState'] ?? 'down',
                    'isisISAdjState' => $adjacency_data['ciiISAdjState'] ?? 'down',
                ];

                if (! empty($adjacency_data)) {
                    $attributes = array_merge($attributes, [
                        'isisISAdjNeighSysType' => Arr::get($this->isis_codes, $adjacency_data['ciiISAdjNeighSysType'] ?? 'unknown', 'unknown'),
                        'isisISAdjNeighSysID' => str_replace(' ', '.', trim($adjacency_data['ciiISAdjNeighSysID'] ?? '')),
                        'isisISAdjNeighPriority' => $adjacency_data['ciiISAdjNeighPriority'] ?? '',
                        'isisISAdjLastUpTime' => $this->parseAdjacencyTime($adjacency_data),
                        'isisISAdjAreaAddress' => str_replace(' ', '.', trim($adjacency_data['ciiISAdjAreaAddress'] ?? '')),
                        'isisISAdjIPAddrType' => $adjacency_data['ciiISAdjIPAddrType'] ?? '',
                        'isisISAdjIPAddrAddress' => (string) IP::fromHexstring($adjacency_data['ciiISAdjIPAddrAddress'] ?? null, true),
                    ]);
                }

                $adjacencies->push(new IsisAdjacency($attributes));
            }
        }

        return $adjacencies;
    }

    public function pollIsIs($adjacencies): Collection
    {
        $data = snmpwalk_cache_twopart_oid($this->getDeviceArray(), 'ciiISAdjState', [], 'CISCO-IETF-ISIS-MIB');

        if (count($data) !== $adjacencies->where('ciiISAdjState', 'up')->count()) {
            echo 'New Adjacencies, running discovery';

            return $this->fillNew($adjacencies, $this->discoverIsIs());
        }

        $data = snmpwalk_cache_twopart_oid($this->getDeviceArray(), 'ciiISAdjLastUpTime', $data, 'CISCO-IETF-ISIS-MIB', null, '-OQUst');

        $adjacencies->each(function (IsisAdjacency $adjacency) use (&$data) {
            $adjacency_data = Arr::last($data[$adjacency->ifIndex]);
            $adjacency->isisISAdjState = $adjacency_data['ciiISAdjState'] ?? $adjacency->isisISAdjState;
            $adjacency->isisISAdjLastUpTime = $this->parseAdjacencyTime($adjacency_data);
            $adjacency->save();
            unset($data[$adjacency->ifIndex]);
        });

        return $adjacencies;
    }

    protected function parseAdjacencyTime($data): int
    {
        return (int) max($data['ciiISAdjLastUpTime'] ?? 1, 1) / 100;
    }
}
