<?php

/**
 * NetscalerSource.php
 *
 * Citrix NetScaler virtual servers
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
use App\Models\NetscalerVserver;
use Illuminate\Support\Collection;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Number;

class NetscalerSource implements LoadBalancerSource
{
    /** @var array<string, int>|null */
    private ?array $counts = null;

    public function __construct(private readonly Device $device)
    {
    }

    public function counts(): array
    {
        return $this->counts ??= array_filter([LoadBalancerSection::VirtualServers->value => $this->device->netscalerVservers()->count()]);
    }

    public function columns(LoadBalancerSection $section): array
    {
        return $section === LoadBalancerSection::VirtualServers ? [
            'type' => __('Type'),
            'traffic_in' => __('Traffic In'),
            'traffic_out' => __('Traffic Out'),
        ] : [];
    }

    public function items(LoadBalancerSection $section): Collection
    {
        if ($section !== LoadBalancerSection::VirtualServers) {
            return new Collection;
        }

        return $this->device->netscalerVservers()
            ->orderBy('vsvr_name')
            ->get()
            ->keyBy('vsvr_id')
            ->toBase()
            ->map(fn (NetscalerVserver $vsvr) => new LoadBalancerItem(
                id: $vsvr->vsvr_id,
                name: (string) $vsvr->vsvr_name,
                severity: match ($vsvr->vsvr_state) {
                    'up' => Severity::Ok,
                    'down' => Severity::Error,
                    'busy', 'outOfService', 'transitionToOutOfService', 'transitionToOutOfServiceDown' => Severity::Warning,
                    default => Severity::Unknown,
                },
                status: (string) $vsvr->vsvr_state,
                address: $vsvr->vsvr_ip . ':' . $vsvr->vsvr_port,
                columns: [
                    'type' => (string) $vsvr->vsvr_type,
                    'traffic_in' => Number::formatSi($vsvr->vsvr_bps_in * 8, 2, 0, 'bps'),
                    'traffic_out' => Number::formatSi($vsvr->vsvr_bps_out * 8, 2, 0, 'bps'),
                ],
                sortValues: [
                    'traffic_in' => $vsvr->vsvr_bps_in,
                    'traffic_out' => $vsvr->vsvr_bps_out,
                ],
                details: [
                    __('Clients') => (string) $vsvr->vsvr_clients,
                    __('Server Connections') => (string) $vsvr->vsvr_server,
                    __('Requests') => $vsvr->vsvr_req_rate . '/s',
                ],
            ));
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
        return $section === LoadBalancerSection::VirtualServers ? [
            'netscalervsvr_bits' => 'Bits',
            'netscalervsvr_pkts' => 'Packets',
            'netscalervsvr_conns' => 'Connections',
            'netscalervsvr_reqs' => 'Requests',
            'netscalervsvr_hitmiss' => 'Hit/Miss',
        ] : [];
    }
}
