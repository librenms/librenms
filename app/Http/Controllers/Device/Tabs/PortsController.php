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
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;

class PortsController implements DeviceTab
{
    private bool $detail = false;

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

    public function data(Device $device): array
    {
        $tab = \Request::segment(4);
        $this->detail = empty($tab) || $tab == 'detail';
        $data = match($tab) {
            'links' => $this->linksData($device),
            'xdsl' => $this->xdslData($device),
            default => $this->portData($device),
        };


        return array_merge([
            'tab' => $tab,
            'details' => empty($tab) || $tab == 'detail',
            'submenu' => [
                $this->getTabs($device),
                'Graphs' => [
                    ['name' => 'Bits', 'url' => 'bits'],
                    ['name' => 'Unicast Packets', 'url' => 'upkts'],
                    ['name' => 'Non-Unicast Packets', 'url' => 'nupkts'],
                    ['name' => 'Errors', 'url' => 'errors'],
                ],
            ],
        ], $data);
    }

    private function portData(Device $device): array
    {
        $relationships = ['groups', 'ipv4', 'ipv6', 'vlans', 'adsl', 'vdsl'];

        if ($this->detail && Config::get('enable_port_relationship')) {
            $relationships[] = 'links';
            $relationships[] = 'pseudowires.endpoints';
            $relationships[] = 'ipv4Networks.ipv4';
            $relationships[] = 'ipv6Networks.ipv6';
        }

        $ports = $device->ports()->isUp()->orderBy('ifIndex')
            ->hasAccess(Auth::user())->with($relationships)->get(); // TODO paginate
        $neighbors = $ports->keyBy('port_id')->map(fn($port) => $this->findPortNeighbors($port));
        $neighbor_ports = Port::with('device')
            ->hasAccess(Auth::user())
            ->whereIn('port_id', $neighbors->map(fn($a) => array_keys($a))->flatten())
            ->get()->keyBy('port_id');

        return [
            'ports' => $ports,
            'neighbors' => $neighbors,
            'neighbor_ports' => $neighbor_ports,
            'graphs' => [
                'bits' => [['type' => 'port_bits', 'title' => trans('Traffic'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
                'upkts' => [['type' => 'port_upkts', 'title' => trans('Packets (Unicast)'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
                'errors' => [['type' => 'port_errors', 'title' => trans('Errors'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]]],
            ],

        ];
    }

    public function findPortNeighbors(Port $port): array
    {
        // if Loopback, skip
        if (! $this->detail || str_contains(strtolower($port->getLabel()), 'loopback')) {
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
                $ids = $port->ipv4Networks->map(fn($net) => $net->ipv4->pluck('port_id'))->flatten();
                foreach ($ids as $port_id) {
                    if ($port_id !== $port->port_id) {
                        $this->addPortNeighbor($neighbors, 'ipv4_network', $port_id);
                    }
                }
            }

            if ($port->ipv6Networks->isNotEmpty()) {
                $ids = $port->ipv6Networks->map(fn($net) => $net->ipv6->pluck('port_id'))->flatten();
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
            ->where(fn($q) => $q->where('port_id_high', $port->port_id)->orWhere('port_id_low', $port->port_id))->get();
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
            ['name' => 'Basic', 'url' => 'basic'],
            ['name' => 'Detail', 'url' => ''],
        ];

        if ($device->macs()->exists()) {
            $tabs[] = ['name' => 'ARP Table', 'url' => 'arp'];
        }

        if ($device->portsFdb()->exists()) {
            $tabs[] = ['name' => 'FDB Table', 'url' => 'fdb'];
        }

        if ($device->links()->exists()) {
            $tabs[] = ['name' => 'Neighbors', 'url' => 'links'];
        }

        if ($device->portsAdsl()->exists() || $device->portsVdsl()->exists()) {
            $tabs[] = ['name' => 'xDSL', 'url' => 'xdsl'];
        }

        return $tabs;
    }
}
