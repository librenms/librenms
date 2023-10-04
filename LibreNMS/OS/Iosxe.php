<?php
/**
 * Iosxe.php
 *
 * Cisco IOS-XE Wireless LAN Controller
 * Cisco IOS-XE ISIS Neighbors
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
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\IsIsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCellDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessChannelDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Polling\IsIsPolling;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS\Traits\CiscoCellular;
use LibreNMS\Util\IP;
use SnmpQuery;

class Iosxe extends Ciscowlc implements
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

    public function pollOS(DataStorageInterface $datastore): void
    {
        // Don't poll Ciscowlc FIXME remove when wireless-controller module exists
    }

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
        $circuits = SnmpQuery::enumStrings()->walk('CISCO-IETF-ISIS-MIB::ciiCirc');
        $adjacencies = new Collection;

        if ($circuits->isValid()) {
            $circuits = $circuits->table(1);
            $adjacencies_data = SnmpQuery::enumStrings()->walk('CISCO-IETF-ISIS-MIB::ciiISAdj')->table(2);

            foreach ($adjacencies_data as $circuit_index => $adjacency_list) {
                foreach ($adjacency_list as $adjacency_index => $adjacency_data) {
                    if (empty($circuits[$circuit_index]['CISCO-IETF-ISIS-MIB::ciiCircIfIndex'])) {
                        continue;
                    }

                    if (($circuits[$circuit_index]['CISCO-IETF-ISIS-MIB::ciiCircPassiveCircuit'] ?? 'true') == 'true') {
                        continue; // Do not poll passive interfaces and bad data
                    }

                    $adjacencies->push(new IsisAdjacency([
                        'device_id' => $this->getDeviceId(),
                        'index' => "[$circuit_index][$adjacency_index]",
                        'ifIndex' => $circuits[$circuit_index]['CISCO-IETF-ISIS-MIB::ciiCircIfIndex'],
                        'port_id' => $this->ifIndexToId($circuits[$circuit_index]['CISCO-IETF-ISIS-MIB::ciiCircIfIndex']),
                        'isisCircAdminState' => $circuits[$circuit_index]['CISCO-IETF-ISIS-MIB::ciiCircAdminState'] ?? 'down',
                        'isisISAdjState' => $adjacency_data['CISCO-IETF-ISIS-MIB::ciiISAdjState'] ?? 'down',
                        'isisISAdjNeighSysType' => Arr::get($this->isis_codes, $adjacency_data['CISCO-IETF-ISIS-MIB::ciiISAdjNeighSysType'] ?? '', 'unknown'),
                        'isisISAdjNeighSysID' => $this->formatIsIsId($adjacency_data['CISCO-IETF-ISIS-MIB::ciiISAdjNeighSysID'] ?? ''),
                        'isisISAdjNeighPriority' => $adjacency_data['CISCO-IETF-ISIS-MIB::ciiISAdjNeighPriority'] ?? '',
                        'isisISAdjLastUpTime' => $this->parseAdjacencyTime($adjacency_data['CISCO-IETF-ISIS-MIB::ciiISAdjLastUpTime'] ?? 0),
                        'isisISAdjAreaAddress' => implode(',', array_map([$this, 'formatIsIsId'], $adjacency_data['CISCO-IETF-ISIS-MIB::ciiISAdjAreaAddress'] ?? [])),
                        'isisISAdjIPAddrType' => implode(',', $adjacency_data['CISCO-IETF-ISIS-MIB::ciiISAdjIPAddrType'] ?? []),
                        'isisISAdjIPAddrAddress' => implode(',', array_map(function ($ip) {
                            return (string) IP::fromHexString($ip, true);
                        }, $adjacency_data['CISCO-IETF-ISIS-MIB::ciiISAdjIPAddrAddress'] ?? [])),
                    ]));
                }
            }
        }

        return $adjacencies;
    }

    public function pollIsIs($adjacencies): Collection
    {
        $states = SnmpQuery::enumStrings()->walk('CISCO-IETF-ISIS-MIB::ciiISAdjState')->values();
        $up_count = array_count_values($states)['up'] ?? 0;

        if ($up_count !== $adjacencies->count()) {
            echo 'New Adjacencies, running discovery';

            return $this->fillNew($adjacencies, $this->discoverIsIs());
        }

        $uptime = SnmpQuery::walk('CISCO-IETF-ISIS-MIB::ciiISAdjLastUpTime')->values();

        return $adjacencies->each(function ($adjacency) use ($states, $uptime) {
            $adjacency->isisISAdjState = $states['CISCO-IETF-ISIS-MIB::ciiISAdjState' . $adjacency->index] ?? $adjacency->isisISAdjState;
            $adjacency->isisISAdjLastUpTime = $this->parseAdjacencyTime($uptime['CISCO-IETF-ISIS-MIB::ciiISAdjLastUpTime' . $adjacency->index] ?? 0);
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
