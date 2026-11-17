<?php

/**
 * CiscoAceSource.php
 *
 * Cisco ACE virtual servers (class maps) and server farms
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs\LoadBalancer;

use App\Http\Controllers\Device\Tabs\LoadBalancer\Data\LoadBalancerItem;
use App\Models\Device;
use App\Models\LoadbalancerRserver;
use App\Models\LoadbalancerVserver;
use Illuminate\Support\Collection;
use LibreNMS\Enum\Severity;

class CiscoAceSource implements LoadBalancerSource
{
    /** @var array<string, int>|null */
    private ?array $counts = null;

    public function __construct(private readonly Device $device)
    {
    }

    public function counts(): array
    {
        return $this->counts ??= array_filter([
            LoadBalancerSection::VirtualServers->value => $this->device->vServers()->count(),
            LoadBalancerSection::Pools->value => $this->device->rServers()->count(),
        ]);
    }

    public function columns(LoadBalancerSection $section): array
    {
        return [];
    }

    public function items(LoadBalancerSection $section): Collection
    {
        return match ($section) {
            LoadBalancerSection::VirtualServers => $this->device->vServers()
                ->orderBy('classmap')
                ->get()
                ->keyBy('classmap_id')
                ->toBase()
                ->map(fn (LoadbalancerVserver $vserver) => new LoadBalancerItem(
                    id: $vserver->classmap_id,
                    name: (string) $vserver->classmap,
                    severity: $vserver->serverstate == 'inService' ? Severity::Ok : Severity::Error,
                    status: (string) $vserver->serverstate,
                )),
            // one row for each server farm, with the state of its real servers
            LoadBalancerSection::Pools => $this->device->rServers()
                ->orderBy('farm_id')
                ->get()
                ->keyBy('rserver_id')
                ->toBase()
                ->map(fn (LoadbalancerRserver $farm) => new LoadBalancerItem(
                    id: $farm->rserver_id,
                    name: (string) $farm->farm_id,
                    severity: $farm->StateDescr == 'Server is now operational' ? Severity::Ok : Severity::Error,
                    status: (string) $farm->StateDescr,
                )),
            default => new Collection,
        };
    }

    public function members(LoadBalancerSection $section, LoadBalancerItem $item): Collection
    {
        return new Collection;
    }

    public function sectionGraphs(LoadBalancerSection $section): array
    {
        return [];
    }

    public function itemGraphs(LoadBalancerSection $section): array
    {
        return match ($section) {
            LoadBalancerSection::VirtualServers => [
                'vserver_bits' => 'Bits',
                'vserver_pkts' => 'Packets',
                'vserver_conns' => 'Connections',
            ],
            LoadBalancerSection::Pools => [
                'rserver_curr' => 'Current Connections',
                'rserver_failed' => 'Failed Connections',
                'rserver_total' => 'Total Connections',
            ],
            default => [],
        };
    }
}
