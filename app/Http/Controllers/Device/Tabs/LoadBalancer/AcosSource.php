<?php

/**
 * AcosSource.php
 *
 * Reads the A10 ACOS SLB components (see includes/discovery/loadbalancers/a10-acos-slb.inc.php)
 * as load balancer sections. ACOS calls pools "Service Groups".
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

namespace App\Http\Controllers\Device\Tabs\LoadBalancer;

use App\Http\Controllers\Device\Tabs\LoadBalancer\Data\LoadBalancerItem;
use App\Models\Component;
use App\Models\Device;
use Illuminate\Support\Collection;
use LibreNMS\Enum\Severity;

class AcosSource implements LoadBalancerSource
{
    private const array TYPES = [
        'virtual-servers' => 'acos-slb-vs',
        'virtual-services' => 'acos-slb-vport',
        'pools' => 'acos-slb-pool',
        'pool-members' => 'acos-slb-member',
        'real-servers' => 'acos-slb-server',
    ];

    private const array SECTION_GRAPHS = [
        'virtual-servers' => [
            'device_acos_slb_allvs_curconns' => 'Current Connections',
            'device_acos_slb_allvs_bytesin' => 'Bytes In',
            'device_acos_slb_allvs_bytesout' => 'Bytes Out',
        ],
        'virtual-services' => [
            'device_acos_slb_allvport_curconns' => 'Current Connections',
            'device_acos_slb_allvport_bytesin' => 'Bytes In',
            'device_acos_slb_allvport_bytesout' => 'Bytes Out',
        ],
    ];

    private const array ITEM_GRAPHS = [
        'virtual-servers' => [
            'device_acos_slb_vs_curconns' => 'Current Connections',
            'device_acos_slb_vs_bytesin' => 'Bytes In',
            'device_acos_slb_vs_bytesout' => 'Bytes Out',
        ],
        'virtual-services' => [
            'device_acos_slb_vport_curconns' => 'Current Connections',
            'device_acos_slb_vport_bytesin' => 'Bytes In',
            'device_acos_slb_vport_bytesout' => 'Bytes Out',
        ],
        'pools' => [
            'device_acos_slb_allpm_curconns' => 'Current Connections',
            'device_acos_slb_allpm_bytesin' => 'Bytes In',
            'device_acos_slb_allpm_bytesout' => 'Bytes Out',
        ],
        'real-servers' => [
            'device_acos_slb_server_curconns' => 'Current Connections',
            'device_acos_slb_server_bytesin' => 'Bytes In',
            'device_acos_slb_server_bytesout' => 'Bytes Out',
        ],
    ];

    /** @var array<string, int>|null */
    private ?array $counts = null;

    /** @var array<string, int>|null pool ids by name */
    private ?array $poolIds = null;

    /** @var array<string, string>|null real server addresses by name */
    private ?array $serverAddresses = null;

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
            LoadBalancerSection::PoolMembers => ['priority' => __('Priority')],
            LoadBalancerSection::RealServers => ['health_monitor' => __('Health Monitor')],
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

                return new LoadBalancerItem(...$this->sectionFields($section, $prefs) + [
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
    private function sectionFields(LoadBalancerSection $section, array $prefs): array
    {
        return match ($section) {
            LoadBalancerSection::VirtualServers => [
                'address' => $prefs['address'] ?? null,
            ],
            LoadBalancerSection::VirtualServices => [
                'address' => isset($prefs['address']) ? $prefs['address'] . ':' . ($prefs['port'] ?? '') : null,
                'pool' => ($prefs['servicegroup'] ?? '') ?: null,
                'poolId' => $this->poolIds()[$prefs['servicegroup'] ?? ''] ?? null,
                'details' => [__('Virtual Server') => $prefs['vsname'] ?? ''],
            ],
            LoadBalancerSection::PoolMembers => [
                'name' => $prefs['servername'] ?? '',
                'address' => isset($this->serverAddresses()[$prefs['servername'] ?? '']) ? $this->serverAddresses()[$prefs['servername']] . ':' . ($prefs['port'] ?? '') : null,
                'pool' => $prefs['poolname'] ?? null,
                'poolId' => $this->poolIds()[$prefs['poolname'] ?? ''] ?? null,
                'columns' => ['priority' => $prefs['priority'] ?? ''],
            ],
            LoadBalancerSection::RealServers => [
                'address' => $prefs['address'] ?? null,
                'columns' => ['health_monitor' => $prefs['healthmonitor'] ?? ''],
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
     * @return array<string, string>
     */
    private function serverAddresses(): array
    {
        return $this->serverAddresses ??= $this->components(self::TYPES['real-servers'])
            ->mapWithKeys(fn (Component $component) => [(string) $component->label => (string) ($component->prefs->pluck('value', 'attribute')['address'] ?? '')])
            ->filter()
            ->all();
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
