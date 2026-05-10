<?php

/**
 * PortsController.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\PortSecurityController;
use App\Models\Device;
use App\Models\Link;
use App\Models\Port;
use App\Models\PortSecurity;
use App\Models\Pseudowire;
use App\Models\UserPref;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use LibreNMS\Interfaces\UI\DeviceTab;

class PortsController implements DeviceTab
{
    private bool $detail = true;
    private array $settings = [];
    private array $defaults = [
        'perPage' => 32,
        'sort' => 'ifIndex',
        'order' => 'asc',
    ];

    public function visible(Device $device): bool
    {
        return Gate::allows('viewAny', Port::class) && $device->ports()->exists();
    }

    public function slug(): string
    {
        return 'ports';
    }

    public function icon(): string
    {
        return 'fa-link';
    }

    public function name(): string
    {
        return __('Ports');
    }

    public function data(Device $device, Request $request): array
    {
        Validator::validate($request->all(), [
            'page' => 'int',
            'perPage' => ['regex:/^(\d+|all)$/'],
            'sort' => 'in:media,mac,port,traffic,speed,index',
            'order' => 'in:asc,desc',
            'from' => ['regex:/^(int|[+-]\d+[hdmy])$/'],
            'to' => ['regex:/^(int|[+-]\d+[hdmy])$/'],
            ...Port::filterValidationRules(),
            ...PortSecurity::filterValidationRules(),
        ]);

        $this->loadSettings($request);
        $tab = $this->parseTab($request);
        $this->detail = $tab == 'detail';
        $data = match ($tab) {
            'links' => $this->linksData($device),
            'transceivers' => $this->transceiversData($device),
            'xdsl' => $this->xdslData($device),
            'portsecurity' => $this->portSecurityData($device),
            'graphs', 'mini_graphs' => $this->graphData($device, $request),
            default => $this->portData($device, $request),
        };

        return array_merge([
            'tab' => $tab,
            'details' => $this->detail,
            'filterFields' => $tab === 'portsecurity'
                ? PortSecurity::filterFieldDefinitions($device->device_id)
                : $this->filterFields($device->device_id),
            'submenu' => [
                $this->getTabs($device),
                __('Graphs') => $this->getGraphLinks(),
            ],
            'dropdownLinks' => [],
            'filter' => $request->array('filter'),
            'perPage' => $this->settings['perPage'],
            'sort' => $this->settings['sort'],
            'next_order' => $this->settings['order'] == 'asc' ? 'desc' : 'asc',
        ], $data);
    }

    private function portData(Device $device, Request $request): array
    {
        $relationships = ['groups', 'ipv4', 'ipv6', 'vlans', 'adsl', 'vdsl'];
        if ($this->detail) {
            $relationships[] = 'links';
            $relationships[] = 'transceivers';
            $relationships[] = 'pseudowires.endpoints';
            $relationships[] = 'ipv6Networks.ipv6';
            $relationships['stackParent'] = fn ($q) => $q->select('port_id');
            $relationships['stackChildren'] = fn ($q) => $q->select('port_id');

            if (LibrenmsConfig::get('ports_ipv4_neighbours') == 'arp') {
                $relationships[] = 'macLinkedPorts';
            } else {
                $relationships[] = 'ipv4Networks.ipv4';
            }
        }

        /** @var Collection<int, Port>|LengthAwarePaginator<Port> $ports */
        $ports = $this->getFilteredPortsQuery($device, $relationships, $request)
            ->paginate(fn ($total) => $this->settings['perPage'] == 'all' ? $total : (int) $this->settings['perPage']) // @phpstan-ignore-line missing closure type
            ->appends('perPage', $this->settings['perPage']);

        $data = [
            'ports' => $ports,
            'neighbors' => $ports->keyBy('port_id')->map(fn (Port $port) => $this->findPortNeighbors($port)),
            'graphs' => [
                'bits' => [['type' => 'port_bits', 'title' => trans('Traffic'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
                'upkts' => [['type' => 'port_upkts', 'title' => trans('Packets (Unicast)'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
                'errors' => [['type' => 'port_errors', 'title' => trans('Errors'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
            ],
        ];

        if ($this->detail) {
            $data['neighbor_ports'] = Port::with('device')
                ->hasAccess(Auth::user())
                ->whereIn('port_id', $data['neighbors']->map(fn ($a) => array_keys($a))->flatten())
                ->get()->keyBy('port_id');
        }

        return $data;
    }

    public function findPortNeighbors(Port $port): array
    {
        // only do for detail
        if (! $this->detail) {
            return [];
        }

        // skip ports that cannot have neighbors
        if (in_array($port->ifType, ['softwareLoopback', 'rs232'])) {
            return [];
        }

        $neighbors = [];

        // Links always included
        // fa-plus black portlink on devicelink
        foreach ($port->links as $link) {
            /** @var Link $link */
            if ($link->remote_port_id) {
                $this->addPortNeighbor($neighbors, 'link', $link->remote_port_id);
            }
        }

        // IPv4 + IPv6 subnet if detailed
        // fa-arrow-right green portlink on devicelink
        $ids = [];
        if (LibrenmsConfig::get('ports_ipv4_neighbours') == 'arp') {
            $ids = $port->macLinkedPorts->where('port_id', '<>', $port->port_id)->pluck('port_id');
        } else {
            $ids = $port->ipv4Networks->map(fn ($net) => $net->ipv4->where('port_id', '<>', $port->port_id)->pluck('port_id'))->flatten();
        }

        foreach ($ids as $port_id) {
            $this->addPortNeighbor($neighbors, 'ipv4_network', $port_id);
        }

        if ($port->ipv6Networks->isNotEmpty()) {
            $ids = $port->ipv6Networks->map(fn ($net) => $net->ipv6->pluck('port_id'))->flatten();
            foreach ($ids as $port_id) {
                if ($port_id !== $port->port_id) {
                    $this->addPortNeighbor($neighbors, 'ipv6_network', $port_id);
                }
            }
        }

        // pseudowires
        // fa-cube green portlink on devicelink: cpwVcID
        /** @var Pseudowire $pseudowire */
        foreach ($port->pseudowires as $pseudowire) {
            foreach ($pseudowire->endpoints as $endpoint) {
                if ($endpoint->port_id != $port->port_id) {
                    $this->addPortNeighbor($neighbors, 'pseudowire', $endpoint->port_id);
                }
            }
        }

        // port stack
        // fa-expand stack_parent: local is a child port
        // fa-compress stack_child: local is a parent port
        foreach ($port->stackParent as $stackParent) {
            $this->addPortNeighbor($neighbors, 'stack_parent', $stackParent->port_id);
        }
        foreach ($port->stackChildren as $stackChild) {
            $this->addPortNeighbor($neighbors, 'stack_child', $stackChild->port_id);
        }

        // PAGP members/parent
        // fa-cube portlink: pagpGroupIfIndex = ifIndex parent
        // fa-cube portlink: if (not parent, pagpGroupIfIndex != ifIndex) ifIndex = pagpGroupIfIndex member
        if ($port->pagpGroupIfIndex) {
            if ($port->pagpGroupIfIndex == $port->ifIndex) {
                $this->addPortNeighbor($neighbors, 'pagp', $port->port_id);
            } else {
                $this->addPortNeighbor($neighbors, 'pagp', $port->pagpParent->port_id);
            }
        }

        return $neighbors;
    }

    private function addPortNeighbor(array &$neighbors, string $type, int $port_id): void
    {
        if (empty($neighbors[$port_id])) {
            $neighbors[$port_id] = [
                'port_id' => $port_id,
            ];
        }

        $neighbors[$port_id][$type] = 1;
    }

    private function graphData(Device $device, Request $request): array
    {
        return [
            'graph_type' => 'port_' . $request->input('type'),
            'ports' => $this->getFilteredPortsQuery($device, [], $request)->get(),
        ];
    }

    private function transceiversData(Device $device): array
    {
        $device->load(['transceivers.port']);

        return [
            'transceivers' => $device->transceivers,
        ];
    }

    private function xdslData(Device $device): array
    {
        $device->portsAdsl->load('port');
        $device->portsVdsl->load('port');

        return [
            'adsl' => $device->portsAdsl->sortBy('port.ifIndex'),
            'vdsl' => $device->portsVdsl->sortBy('port.ifIndex'),
        ];
    }

    private function linksData(Device $device): array
    {
        $device->links->load(['port', 'remotePort', 'remoteDevice']);

        return ['links' => $device->links];
    }

    private function portSecurityData(Device $device): array
    {
        return [
            'portSecurity' => PortSecurityController::paginateForDevice(
                $device->device_id,
                $this->settings['perPage']
            ),
        ];
    }

    private function getTabs(Device $device): array
    {
        $tabs = [
            ['name' => __('Basic'), 'url' => 'basic', 'class' => 'sync-filter-url'],
            ['name' => __('Detail'), 'url' => 'detail', 'class' => 'sync-filter-url'],
        ];

        if ($device->macs()->exists()) {
            $tabs[] = ['name' => __('port.tabs.arp'), 'url' => 'arp'];
        }

        if ($device->nd()->exists()) {
            $tabs[] = ['name' => __('port.tabs.nd'), 'url' => 'nd'];
        }

        if ($device->portsFdb()->exists()) {
            $tabs[] = ['name' => __('port.tabs.fdb'), 'url' => 'fdb'];
        }

        if ($device->transceivers()->exists()) {
            $tabs[] = ['name' => __('port.tabs.transceivers'), 'url' => 'transceivers'];
        }

        if ($device->links()->exists()) {
            $tabs[] = ['name' => __('port.tabs.links'), 'url' => 'links'];
        }

        if ($device->portsAdsl()->exists() || $device->portsVdsl()->exists()) {
            $tabs[] = ['name' => __('port.tabs.xdsl'), 'url' => 'xdsl'];
        }

        if (PortSecurity::where('device_id', $device->device_id)->exists()) {
            $tabs[] = ['name' => __('Port Security'), 'url' => 'portsecurity'];
        }

        return $tabs;
    }

    /**
     * @return array[]
     */
    private function getGraphLinks(): array
    {
        $graph_links = [
            [
                'name' => __('port.graphs.bits'),
                'url' => 'graphs?type=bits',
                'class' => 'sync-filter-url',
                'sub_name' => __('Mini'),
                'sub_url' => 'mini_graphs?type=bits',
            ],
            [
                'name' => __('port.graphs.upkts'),
                'url' => 'graphs?type=upkts',
                'class' => 'sync-filter-url',
                'sub_name' => __('Mini'),
                'sub_url' => 'mini_graphs?type=upkts',
            ],
            [
                'name' => __('port.graphs.nupkts'),
                'url' => 'graphs?type=nupkts',
                'class' => 'sync-filter-url',
                'sub_name' => __('Mini'),
                'sub_url' => 'mini_graphs?type=nupkts',
            ],
            [
                'name' => __('port.graphs.errors'),
                'url' => 'graphs?type=errors',
                'class' => 'sync-filter-url',
                'sub_name' => __('Mini'),
                'sub_url' => 'mini_graphs?type=errors',
            ],
        ];

        if (LibrenmsConfig::get('enable_ports_etherlike')) {
            $graph_links[] = [
                'name' => __('port.graphs.etherlike'),
                'url' => 'graphs?type=etherlike',
                'sub_name' => __('Mini'),
                'sub_url' => 'mini_graphs?type=etherlike',
            ];
        }

        return $graph_links;
    }

    private function loadSettings(Request $request): void
    {
        $input = $request->only(['perPage', 'sort', 'order']);
        $saved = UserPref::getPref($request->user(), 'ports_ui_settings');

        if ($saved === null) {
            $saved = [];
        } elseif (array_key_exists('admin', $saved)) {
            $saved = $this->migrateFilterSettings($saved, $request);
        }

        $this->settings = $input + $saved + $this->defaults;

        if ($this->settings != $saved) {
            if ($this->settings == $this->defaults) {
                UserPref::forgetPref($request->user(), 'ports_ui_settings');
            } else {
                UserPref::setPref($request->user(), 'ports_ui_settings', $this->settings);
            }
        }
    }

    private function getFilteredPortsQuery(Device $device, array $relationships = [], ?Request $request = null): Builder
    {
        $orderBy = match ($this->settings['sort']) {
            'traffic' => \DB::raw('ports.ifInOctets_rate + ports.ifOutOctets_rate'),
            'speed' => 'ifSpeed',
            'media' => 'ifType',
            'mac' => 'ifPhysAddress',
            'port' => 'ifName',
            default => 'ifIndex',
        };

        return Port::where('device_id', $device->device_id)
            ->isNotDeleted()
            ->hasAccess(Auth::user())->with($relationships)
            ->when($request->array('filter'), fn (Builder $q, $filters) => $q->applyFilters($filters))
            ->when($this->settings['sort'] == 'port', fn (Builder $q, $sort) => $q
                ->orderByRaw('SOUNDEX(ifName) ' . $this->settings['order'])
                ->orderByRaw('CHAR_LENGTH(ifName) ' . $this->settings['order'])
                ->orderByRaw('lower(ifName) ' . $this->settings['order'])
            )
            ->orderBy($orderBy, $this->settings['order']);
    }

    /**
     * get the ports sub tab name including handling legacy urls
     */
    private function parseTab(Request $request): string
    {
        if (preg_match('#view=([^/]+)#', $request->fullUrl(), $matches)) {
            return match ($matches[1]) {
                'neighbours' => 'links',
                default => $matches[1],
            };
        }

        return $request->route('vars', LibrenmsConfig::get('ports_page_default')); // fourth segment is called vars to handle legacy urls
    }

    private function migrateFilterSettings(array $saved, Request $request): array
    {
        $filter = [];

        if (! $saved['disabled']) {  // 0: disabled hidden 1: not filtered
            $filter['disabled'] = ['eq' => 0];
        }

        if (! $saved['ignored']) { // 0: ignored hidden 1: not filtered
            $filter['ignore'] = ['eq' => 0];
        }

        if ($saved['status'] == 'up') { // up: only status up, any: not filtered
            $filter['state'] = ['eq' => 'up'];
        } elseif ($saved['admin'] == 'up') { // up: only != shutdown, any: not filtered
            $filter['state'] = ['neq' => 'shutdown'];
        }

        Arr::forget($saved, ['admin', 'status', 'disabled', 'ignored']);
        UserPref::setPref($request->user(), 'ports_ui_settings', $saved);
        UserPref::setPref($request->user(), 'filters.device.ports', $filter);

        $request->merge([
            'filter' => array_merge($filter, $request->array('filter')),
        ]);

        return $saved;
    }

    private function filterFields(int $device_id): array
    {
        return [
            [
                'key' => 'search',
                'label' => __('Description'),
                'type' => 'text',
            ],
            [
                'key' => 'state',
                'label' => __('port.oper_status'),
                'type' => 'select',
                'options' => [
                    'up' => __('Up'),
                    'down' => __('Down'),
                    'shutdown' => __('Shutdown'),
                ],
            ],
            [
                'key' => 'ifSpeed',
                'label' => __('port.speed'),
                'type' => 'select',
                'endpoint' => route('ajax.select.port-field'),
                'params' => [
                    'field' => 'ifSpeed',
                    'device' => $device_id,
                ],
            ],
            [
                'key' => 'ifType',
                'label' => __('port.media'),
                'type' => 'select',
                'endpoint' => route('ajax.select.port-field'),
                'params' => [
                    'field' => 'ifType',
                    'device' => $device_id,
                ],
            ],
            [
                'key' => 'ifDuplex',
                'label' => __('port.duplex'),
                'type' => 'select',
                'options' => [
                    'fullDuplex' => __('port.duplex_full'),
                    'halfDuplex' => __('port.duplex_half'),
                    'unknown' => __('port.duplex_unknown'),
                ],
            ],
            [
                'key' => 'port_type',
                'label' => __('port.port_type'),
                'type' => 'select',
                'endpoint' => route('ajax.select.port-field'),
                'params' => [
                    'field' => 'port_descr_type',
                    'device' => $device_id,
                ],
            ],
            [
                'key' => 'errors',
                'label' => __('port.errors'),
                'type' => 'boolean',
            ],
            [
                'key' => 'ignore',
                'label' => __('Ignored'),
                'type' => 'boolean',
            ],
            [
                'key' => 'disabled',
                'label' => __('Disabled'),
                'type' => 'boolean',
            ],
            [
                'key' => 'deleted',
                'label' => __('Deleted'),
                'type' => 'boolean',
            ],
        ];
    }
}
