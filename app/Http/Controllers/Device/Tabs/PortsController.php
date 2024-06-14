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

use App\Models\Device;
use App\Models\Link;
use App\Models\Port;
use App\Models\Pseudowire;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;

class PortsController implements DeviceTab
{
    private bool $detail = false;
    private int $perPage = 15;
    private string $sortOrder = 'asd';

    public function visible(Device $device): bool
    {
        return $device->ports()->exists();
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
            'perPage' => 'int',
            'sort' => 'in:media,mac,port,traffic,speed',
            'order' => 'in:asc,desc',
            'disabled' => 'in:0,1',
            'ignore' => 'in:0,1',
            'admin' => 'in:up,down,testing,any',
            'status' => 'in:up,down,testing,unknown,dormant,notPresent,lowerLayerDown,any',
            'type' => 'in:bits,upkts,nupkts,errors,etherlike',
            'from' => ['regex:/^(int|[+-]\d+[hdmy])$/'],
            'to' => ['regex:/^(int|[+-]\d+[hdmy])$/'],
        ]);

        $tab = $request->segment(4);
        $this->detail = empty($tab) || $tab == 'detail';
        $data = match ($tab) {
            'links' => $this->linksData($device),
            'xdsl' => $this->xdslData($device),
            'graphs', 'mini_graphs' => $this->graphData($device, $request),
            default => $this->portData($device, $request),
        };

        $disabled = $request->input('disabled');
        $ignore = $request->input('ignore');
        $admin = $request->input('admin') == 'any';
        $status = $request->input('status') == 'up';

        return array_merge([
            'tab' => $tab,
            'details' => empty($tab) || $tab == 'detail',
            'submenu' => [
                $this->getTabs($device),
                __('Graphs') => $this->getGraphLinks(),
                __('Mini Graphs') => $this->getGraphLinks('mini_graphs'),
            ],
            'page_links' => [
                [
                    'icon' => $status ? 'fa-regular fa-square-check' : 'fa-regular fa-square',
                    'url' => $status ? $request->fullUrlWithoutQuery('status') : $request->fullUrlWithQuery(['status' => 'up']),
                    'title' => __('port.filters.status_up'),
                    'external' => false,
                ],
                [
                    'icon' => $admin ? 'fa-regular fa-square-check' : 'fa-regular fa-square',
                    'url' => $admin ? $request->fullUrlWithoutQuery('admin') : $request->fullUrlWithQuery(['admin' => 'any']),
                    'title' => __('port.filters.admin_down'),
                    'external' => false,
                ],
                [
                    'icon' => $disabled ? 'fa-regular fa-square-check' : 'fa-regular fa-square',
                    'url' => $disabled ? $request->fullUrlWithoutQuery('disabled') : $request->fullUrlWithQuery(['disabled' => 1]),
                    'title' => __('port.filters.disabled'),
                    'external' => false,
                ],
                [
                    'icon' => $ignore ? 'fa-regular fa-square-check' : 'fa-regular fa-square',
                    'url' => $ignore ? $request->fullUrlWithoutQuery('ignore') : $request->fullUrlWithQuery(['ignore' => 1]),
                    'title' => __('port.filters.ignored'),
                    'external' => false,
                ],
            ],
            'perPage' => $this->perPage,
            'sort' => $this->sortOrder,
            'next_order' => $this->sortOrder == 'asc' ? 'desc' : 'asc',
        ], $data);
    }

    private function portData(Device $device, Request $request): array
    {
        $relationships = ['groups', 'ipv4', 'ipv6', 'vlans', 'adsl', 'vdsl'];
        if ($this->detail) {
            $relationships[] = 'links';
            $relationships[] = 'pseudowires.endpoints';
            $relationships[] = 'ipv4Networks.ipv4';
            $relationships[] = 'ipv6Networks.ipv6';
        }

        $ports = $this->getFilteredPortsQuery($device, $request, $relationships)->paginate($this->perPage);

        $data = [
            'ports' => $ports,
            'neighbors' => $ports->keyBy('port_id')->map(fn ($port) => $this->findPortNeighbors($port)),
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

        if ($this->detail) {
            // IPv4 + IPv6 subnet if detailed
            // fa-arrow-right green portlink on devicelink
            if ($port->ipv4Networks->isNotEmpty()) {
                $ids = $port->ipv4Networks->map(fn ($net) => $net->ipv4->pluck('port_id'))->flatten();
                foreach ($ids as $port_id) {
                    if ($port_id !== $port->port_id) {
                        $this->addPortNeighbor($neighbors, 'ipv4_network', $port_id);
                    }
                }
            }

            if ($port->ipv6Networks->isNotEmpty()) {
                $ids = $port->ipv6Networks->map(fn ($net) => $net->ipv6->pluck('port_id'))->flatten();
                foreach ($ids as $port_id) {
                    if ($port_id !== $port->port_id) {
                        $this->addPortNeighbor($neighbors, 'ipv6_network', $port_id);
                    }
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
        // fa-expand portlink: local is low port
        // fa-compress portlink: local is high portPort
        $stacks = \DB::table('ports_stack')->where('device_id', $port->device_id)
            ->where(fn ($q) => $q->where('port_id_high', $port->port_id)->orWhere('port_id_low', $port->port_id))->get();
        foreach ($stacks as $stack) {
            if ($stack->port_id_low) {
                $this->addPortNeighbor($neighbors, 'stack_low', $stack->port_id_low);
            }
            if ($stack->port_id_high) {
                $this->addPortNeighbor($neighbors, 'stack_high', $stack->port_id_high);
            }
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
            'graph_type' => 'port_' . $request->get('type'),
            'ports' => $this->getFilteredPortsQuery($device, $request)->get(),
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

    private function getTabs(Device $device): array
    {
        $tabs = [
            ['name' => __('Basic'), 'url' => 'basic'],
            ['name' => __('Detail'), 'url' => ''],
        ];

        if ($device->macs()->exists()) {
            $tabs[] = ['name' => __('port.tabs.arp'), 'url' => 'arp'];
        }

        if ($device->portsFdb()->exists()) {
            $tabs[] = ['name' => __('port.tabs.fdb'), 'url' => 'fdb'];
        }

        if ($device->links()->exists()) {
            $tabs[] = ['name' => __('port.tabs.links'), 'url' => 'links'];
        }

        if ($device->portsAdsl()->exists() || $device->portsVdsl()->exists()) {
            $tabs[] = ['name' => __('port.tabs.xdsl'), 'url' => 'xdsl'];
        }

        return $tabs;
    }

    /**
     * @return array[]
     */
    private function getGraphLinks(string $urlSlug = 'graphs'): array
    {
        $graph_links = [
            ['name' => __('port.graphs.bits'), 'url' => $urlSlug . '?type=bits'],
            ['name' => __('port.graphs.upkts'), 'url' => $urlSlug . '?type=upkts'],
            ['name' => __('port.graphs.nupkts'), 'url' => $urlSlug . '?type=nupkts'],
            ['name' => __('port.graphs.errors'), 'url' => $urlSlug . '?type=errors'],
        ];

        if (Config::get('enable_ports_etherlike')) {
            $graph_links[] = ['name' => __('port.graphs.etherlike'), 'url' => $urlSlug . '?type=etherlike'];
        }

        return $graph_links;
    }

    private function getFilteredPortsQuery(Device $device, Request $request, array $relationships = []): HasMany
    {
        $this->perPage = $request->input('perPage', 15);
        $this->sortOrder = $request->input('order', 'asc');
        $orderBy = match ($request->input('sort', 'port')) {
            'traffic' => \DB::raw('ports.ifInOctets_rate + ports.ifOutOctets_rate'),
            'speed' => 'ifSpeed',
            'media' => 'ifType',
            'mac' => 'ifPhysAddress',
            default => 'ifIndex',
        };

        return $device->ports()
            ->isNotDeleted()
            ->when(!$request->input('disabled'), fn(Builder $q, $disabled) => $q->where('disabled', 0))
            ->when(!$request->input('ignore'), fn(Builder $q, $disabled) => $q->where('ignore', 0))
            ->when($request->input('admin') != 'any', fn(Builder $q, $admin) => $q->where('ifAdminStatus', $request->input('admin', 'up')))
            ->when($request->input('status', 'any') != 'any', fn(Builder $q, $admin) => $q->where('ifOperStatus', $request->input('status')))
            ->orderBy($orderBy, $this->sortOrder)
            ->hasAccess(Auth::user())->with($relationships);
    }
}
