<?php

/**
 * F5Source.php
 *
 * F5 BIG-IP LTM and GTM objects, stored as components with prefs by discovery
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
use App\Models\Component;
use App\Models\Device;
use Illuminate\Support\Collection;
use LibreNMS\Enum\Severity;

class F5Source implements LoadBalancerSource
{
    private const array TYPES = [
        'virtual-servers' => 'f5-ltm-vs',
        'pools' => 'f5-ltm-pool',
        'pool-members' => 'f5-ltm-poolmember',
        'bandwidth-controllers' => 'f5-ltm-bwc',
        'wide-ips' => 'f5-gtm-wide',
        'gslb-pools' => 'f5-gtm-pool',
        'certificates' => 'f5-cert',
    ];

    private const array SECTION_GRAPHS = [
        'virtual-servers' => [
            'device_bigip_ltm_allvs_currconns' => 'Current Connections',
            'device_bigip_ltm_allvs_conns' => 'Connections',
            'device_bigip_ltm_allvs_bytesin' => 'Bytes In',
            'device_bigip_ltm_allvs_bytesout' => 'Bytes Out',
            'device_bigip_ltm_allvs_pktsin' => 'Packets In',
            'device_bigip_ltm_allvs_pktsout' => 'Packets Out',
        ],
        'bandwidth-controllers' => [
            'device_bigip_ltm_allbwc_BitsDropped' => 'Traffic Dropped',
            'device_bigip_ltm_allbwc_Bitsin' => 'Traffic In',
            'device_bigip_ltm_allbwc_pktsin' => 'Packets In',
        ],
        'wide-ips' => [
            'device_bigip_gtm_allwide_requests' => 'Total Requests',
            'device_bigip_gtm_allwide_resolved' => 'Resolved Requests',
            'device_bigip_gtm_allwide_dropped' => 'Dropped Requests',
        ],
        'gslb-pools' => [
            'device_bigip_gtm_allpool_requests' => 'Resolved Requests',
            'device_bigip_gtm_allpool_dropped' => 'Dropped Requests',
        ],
    ];

    private const array ITEM_GRAPHS = [
        'virtual-servers' => [
            'device_bigip_ltm_vs_currconns' => 'Current Connections',
            'device_bigip_ltm_vs_conns' => 'Connections',
            'device_bigip_ltm_vs_bytes' => 'Bytes',
            'device_bigip_ltm_vs_pkts' => 'Packets',
        ],
        'pools' => [
            'device_bigip_ltm_allpm_currconns' => 'Current Connections',
            'device_bigip_ltm_allpm_conns' => 'Connections',
            'device_bigip_ltm_allpm_bytesin' => 'Bytes In',
            'device_bigip_ltm_allpm_bytesout' => 'Bytes Out',
            'device_bigip_ltm_allpm_pktsin' => 'Packets In',
            'device_bigip_ltm_allpm_pktsout' => 'Packets Out',
        ],
        'bandwidth-controllers' => [
            'device_bigip_ltm_bwc_BitsDropped' => 'Traffic Dropped',
            'device_bigip_ltm_bwc_Bits' => 'Traffic In',
            'device_bigip_ltm_bwc_pkts' => 'Packets In',
        ],
        'wide-ips' => [
            'device_bigip_gtm_wide_requests' => 'Total Requests',
            'device_bigip_gtm_wide_resolved' => 'Resolved Requests',
            'device_bigip_gtm_wide_dropped' => 'Dropped Requests',
        ],
        'gslb-pools' => [
            'device_bigip_gtm_pool_requests' => 'Resolved Requests',
            'device_bigip_gtm_pool_dropped' => 'Dropped Requests',
        ],
    ];

    /** ltmPoolMinUpAction values */
    private const array POOL_DOWN_ACTIONS = ['Unused', 'Reboot', 'Restart', 'Failover', 'Failover and Restart', 'Go Active', 'None'];

    /** @var array<string, int>|null pool ids by name */
    private ?array $poolIds = null;

    /** @var array<string, int>|null */
    private ?array $counts = null;

    public function __construct(private readonly Device $device)
    {
    }

    public function counts(): array
    {
        if ($this->counts !== null) {
            return $this->counts;
        }

        $counts = $this->device->components()
            ->whereIn('type', self::TYPES)
            ->where('disabled', 0)
            ->groupBy('type')
            ->selectRaw('type, count(*) as total')
            ->pluck('total', 'type');

        return $this->counts = collect(self::TYPES)
            ->map(fn (string $type) => (int) ($counts[$type] ?? 0))
            ->filter()
            ->all();
    }

    public function columns(LoadBalancerSection $section): array
    {
        return match ($section) {
            LoadBalancerSection::Pools => [
                'monitor' => __('Monitor'),
                'active_members' => __('Active Members'),
                'minimum_members' => __('Minimum Members'),
            ],
            LoadBalancerSection::Certificates => ['days_left' => __('Days Left')],
            default => [],
        };
    }

    public function items(LoadBalancerSection $section): Collection
    {
        if (! isset(self::TYPES[$section->value])) {
            return new Collection;
        }

        return $this->components(self::TYPES[$section->value])
            ->map(function (Component $component) use ($section): LoadBalancerItem {
                /** @var array<string, string> $prefs */
                $prefs = $component->prefs->pluck('value', 'attribute')->all();

                return new LoadBalancerItem(...$this->sectionFields($section, (string) $component->label, $prefs) + [
                    'id' => $component->id,
                    'name' => (string) $component->label,
                    'severity' => match ((int) $component->status) {
                        0 => Severity::Ok,
                        1 => Severity::Warning,
                        default => Severity::Error,
                    },
                    'status' => $component->status ? ($component->error ?: __('Error')) : __('Ok'),
                ]);
            });
    }

    public function members(LoadBalancerSection $section, LoadBalancerItem $item): Collection
    {
        if ($section !== LoadBalancerSection::Pools) {
            return new Collection;
        }

        return $this->items(LoadBalancerSection::PoolMembers)->where('pool', $item->name);
    }

    public function sectionGraphs(LoadBalancerSection $section): array
    {
        return self::SECTION_GRAPHS[$section->value] ?? [];
    }

    public function itemGraphs(LoadBalancerSection $section): array
    {
        return self::ITEM_GRAPHS[$section->value] ?? [];
    }

    /**
     * The section specific LoadBalancerItem arguments
     *
     * @param  array<string, string>  $prefs
     * @return array<string, mixed>
     */
    private function sectionFields(LoadBalancerSection $section, string $label, array $prefs): array
    {
        $address = isset($prefs['IP']) ? $prefs['IP'] . ':' . ($prefs['port'] ?? '') : null;

        return match ($section) {
            LoadBalancerSection::VirtualServers => [
                'address' => $address,
                'pool' => $prefs['pool'] ?? null,
                'poolId' => $this->poolIds()[$prefs['pool'] ?? ''] ?? null,
            ],
            LoadBalancerSection::Pools => [
                'columns' => [
                    'monitor' => $prefs['monitor'] ?? '',
                    'active_members' => $prefs['currentup'] ?? '',
                    'minimum_members' => $prefs['minup'] ?? '',
                ],
                'details' => [
                    __('Pool Down Action') => self::POOL_DOWN_ACTIONS[(int) ($prefs['minupaction'] ?? 0)] ?? '',
                ],
            ],
            // a pool member's label is the name of its pool
            LoadBalancerSection::PoolMembers => [
                'name' => ($prefs['nodename'] ?? '') ?: $label,
                'address' => $address,
                'pool' => $label,
                'poolId' => $this->poolIds()[$label] ?? null,
            ],
            LoadBalancerSection::Certificates => [
                'columns' => ['days_left' => $prefs['daysLeft'] ?? ''],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, int>
     */
    private function poolIds(): array
    {
        return $this->poolIds ??= $this->components(self::TYPES['pools'])->map->label->flip()->all();
    }

    /**
     * Enabled components of a type with their prefs, keyed by id
     *
     * @return Collection<int, Component>
     */
    private function components(string $type): Collection
    {
        return $this->device->components()
            ->where('type', $type)
            ->where('disabled', 0)
            ->with('prefs')
            ->orderBy('label')
            ->get()
            ->keyBy('id')
            ->toBase();
    }
}
