<?php

/**
 * RoutingController.php
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
use App\Models\Component;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Number;

class RoutingController implements DeviceTab
{
    public function getRoutingTabs(Device $device): array
    {
        if (Gate::none(['routing.view', 'routing.viewAll'])) {
            return [];
        }

        return array_filter([
            'ospf' => $device->ospfInstances()->count(),
            'ospfv3' => $device->ospfv3Instances()->count(),
            'isis' => $device->isisAdjacencies()->count(),
            'bgp' => $device->bgppeers()->count(),
            'vrf' => $device->vrfs()->count(),
            'cef' => $device->cefSwitching()->count(),
            'mpls' => $device->mplsServices()->count(),
            'cisco-otv' => Component::query()->where('device_id', $device->device_id)->where('type', 'Cisco-OTV')->count(),
            'loadbalancer_rservers' => $device->rServers()->count(),
            'ipsec_tunnels' => $device->ipsecTunnels()->count(),
            'routes' => $device->routes()->count(),
        ]);
    }

    public function visible(Device $device): bool
    {
        return ! empty($this->getRoutingTabs($device));
    }

    public function slug(): string
    {
        return 'routing';
    }

    public function icon(): string
    {
        return 'fa-random';
    }

    public function name(): string
    {
        return __('Routing');
    }

    public function data(Device $device, Request $request): array
    {
        Gate::authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        $validProtos = [
            'ospf',
            'ospfv3',
            'isis',
            'bgp',
            'vrf',
            'cef',
            'mpls',
            'cisco-otv',
            'loadbalancer_rservers',
            'ipsec_tunnels',
            'routes',
        ];

        $validViews = [
            'basic',
            'graphs',
            'updates',
            'prefixes_ipv4unicast',
            'prefixes_ipv4vpn',
            'prefixes_ipv6unicast',
            'prefixes_ipv6vpn',
            'macaccounting_bits',
            'macaccounting_pkts',
            'lsp',
            'paths',
            'sdps',
            'sdpbinds',
            'services',
            'saps',
        ];

        $validGraphs = ['bits', 'pkts', 'upkts', 'nupkts', 'errors'];

        Validator::validate($request->all(), [
            'proto' => 'nullable|in:' . implode(',', $validProtos),
            'section' => 'nullable|in:' . implode(',', $validProtos),
            'view' => 'nullable|in:' . implode(',', $validViews),
            'graph' => 'nullable|in:' . implode(',', $validGraphs),
        ]);

        $routingTabs = $this->getRoutingTabs($device);
        $tabLabels = $this->getTabLabels();

        $proto = $request->query('proto') ?? $request->query('section') ?? array_key_first($routingTabs) ?? 'cef';

        $options = [];
        foreach ($routingTabs as $type => $typeCount) {
            $label = ($tabLabels[$type] ?? ucfirst($type)) . ' (' . $typeCount . ')';
            $options[$type] = [
                'text' => $label,
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => $type]),
            ];
        }

        $subData = match ($proto) {
            'cef' => $this->cefData($device, $request),
            'ipsec_tunnels' => $this->ipsecTunnelsData($device, $request),
            'vrf' => $this->vrfData($device, $request),
            'cisco-otv' => $this->ciscoOtvData($device, $request),
            'ospf' => $this->ospfData($device, $request),
            'ospfv3' => $this->ospfv3Data($device, $request),
            'isis' => $this->isisData($device, $request),
            'routes' => $this->routesData($device, $request),
            'bgp' => $this->bgpData($device, $request),
            'mpls' => $this->mplsData($device, $request),
            default => [],
        };

        return array_merge([
            'proto' => $proto,
            'routing_tabs' => $routingTabs,
            'options' => $options,
        ], $subData);
    }

    private function getTabLabels(): array
    {
        return [
            'ipsec_tunnels' => __('IPSEC Tunnels'),
            'loadbalancer_rservers' => __('Rservers'),
            'loadbalancer_vservers' => __('Serverfarms'),
            'netscaler_vsvr' => __('VServers'),
            'bgp' => __('BGP'),
            'cef' => __('CEF'),
            'ospf' => __('OSPF'),
            'ospfv3' => __('OSPFv3'),
            'isis' => __('ISIS'),
            'vrf' => __('VRFs'),
            'routes' => __('Routing Table'),
            'cisco-otv' => __('OTV'),
            'mpls' => __('MPLS'),
        ];
    }

    private function cefData(Device $device, Request $request): array
    {
        $view = $request->query('view', 'basic');
        if (! in_array($view, ['basic', 'graphs'], true)) {
            $view = 'basic';
        }

        $cefRows = $device->cefSwitching()
            ->orderBy('entPhysicalIndex')
            ->orderBy('afi')
            ->orderBy('cef_index')
            ->get();

        $entities = $device->entityPhysical()->get()->keyBy('entPhysicalIndex');

        $rows = $cefRows->map(function ($cef) use ($entities) {
            $entity = $entities->get($cef->entPhysicalIndex);
            if ($entity) {
                if (! $entity->entPhysicalModelName && $entity->entPhysicalContainedIn) {
                    $parent = $entities->get($entity->entPhysicalContainedIn);
                    $entityDescr = $entity->entPhysicalName . ($parent && $parent->entPhysicalModelName ? ' (' . $parent->entPhysicalModelName . ')' : '');
                } else {
                    $entityDescr = $entity->entPhysicalName . ($entity->entPhysicalModelName ? ' (' . $entity->entPhysicalModelName . ')' : '');
                }
            } else {
                $entityDescr = __('Index') . ' ' . $cef->entPhysicalIndex;
            }

            $interval = (int) ($cef->updated - $cef->updated_prev);

            $pathTitle = match ($cef->cef_path) {
                'RP RIB' => __('Process switching with CEF assistance.'),
                'RP LES' => __('Low-end switching. Centralized CEF switch path.'),
                'RP PAS' => __('CEF turbo switch path.'),
                default => null,
            };

            return [
                'id' => $cef->cef_switching_id,
                'entity_descr' => $entityDescr,
                'afi' => $cef->afi,
                'path' => $cef->cef_path,
                'path_title' => $pathTitle,
                'drop' => Number::formatSi($cef->drop, 2, 0, ''),
                'drop_rate' => ($interval > 0 && $cef->drop > $cef->drop_prev) ? round(($cef->drop - $cef->drop_prev) / $interval, 2) : null,
                'punt' => Number::formatSi($cef->punt, 2, 0, ''),
                'punt_rate' => ($interval > 0 && $cef->punt > $cef->punt_prev) ? round(($cef->punt - $cef->punt_prev) / $interval, 2) : null,
                'punt2host' => Number::formatSi($cef->punt2host, 2, 0, ''),
                'punt2host_rate' => ($interval > 0 && $cef->punt2host > $cef->punt2host_prev) ? round(($cef->punt2host - $cef->punt2host_prev) / $interval, 2) : null,
            ];
        });

        return [
            'view' => $view,
            'cef_options' => [
                'basic' => [
                    'text' => __('Basic'),
                    'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'cef', 'view' => 'basic']),
                ],
                'graphs' => [
                    'text' => __('Graphs'),
                    'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'cef', 'view' => 'graphs']),
                ],
            ],
            'cef_rows' => $rows,
        ];
    }

    private function ipsecTunnelsData(Device $device, Request $request): array
    {
        $view = $request->query('view', 'basic');
        $graph = $request->query('graph');
        if ($view === 'graphs' && empty($graph)) {
            $graph = 'bits';
        }

        $selectedOption = $view === 'graphs' ? $graph : 'basic';

        $tunnels = $device->ipsecTunnels()->orderBy('peer_addr')->get()->map(function ($tunnel) {
            return [
                'id' => $tunnel->tunnel_id,
                'local_addr' => preg_replace('/\b0+(?=\d)/', '', (string) $tunnel->local_addr),
                'peer_addr' => preg_replace('/\b0+(?=\d)/', '', (string) $tunnel->peer_addr),
                'tunnel_name' => $tunnel->tunnel_name,
                'tunnel_status' => $tunnel->tunnel_status,
                'status_label' => $tunnel->tunnel_status === 'active' ? 'success' : 'warning',
            ];
        });

        return [
            'view' => $view,
            'graph' => $graph,
            'selected_option' => $selectedOption,
            'ipsec_options' => [
                'basic' => [
                    'text' => __('Basic'),
                    'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'ipsec_tunnels', 'view' => 'basic']),
                ],
                'bits' => [
                    'text' => __('Bits'),
                    'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'ipsec_tunnels', 'view' => 'graphs', 'graph' => 'bits']),
                ],
                'pkts' => [
                    'text' => __('Packets'),
                    'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'ipsec_tunnels', 'view' => 'graphs', 'graph' => 'pkts']),
                ],
            ],
            'tunnels' => $tunnels,
        ];
    }

    private function vrfData(Device $device, Request $request): array
    {
        $view = $request->query('view', 'basic');
        $graph = $request->query('graph');
        if ($view === 'graphs' && empty($graph)) {
            $graph = 'bits';
        }

        $selectedOption = $view === 'graphs' ? $graph : 'basic';

        $vrfs = $device->vrfs()
            ->with(['ports' => fn ($q) => $q->hasAccess($request->user())])
            ->orderBy('vrf_name')
            ->get();

        return [
            'view' => $view,
            'graph' => $graph,
            'selected_option' => $selectedOption,
            'vrf_options' => [
                'basic' => [
                    'text' => __('Basic'),
                    'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'vrf', 'view' => 'basic']),
                ],
                'bits' => [
                    'text' => __('Bits'),
                    'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'vrf', 'view' => 'graphs', 'graph' => 'bits']),
                ],
                'upkts' => [
                    'text' => __('Unicast Packets'),
                    'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'vrf', 'view' => 'graphs', 'graph' => 'upkts']),
                ],
                'nupkts' => [
                    'text' => __('Non-Unicast Packets'),
                    'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'vrf', 'view' => 'graphs', 'graph' => 'nupkts']),
                ],
                'errors' => [
                    'text' => __('Errors'),
                    'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'vrf', 'view' => 'graphs', 'graph' => 'errors']),
                ],
            ],
            'vrfs' => $vrfs,
        ];
    }

    private function ciscoOtvData(Device $device, Request $request): array
    {
        $component = new \LibreNMS\Component();
        $components = $component->getComponents($device->device_id, [
            'filter' => ['ignore' => ['=', 0]],
            'type' => 'Cisco-OTV',
        ]);
        $deviceComponents = $components[$device->device_id] ?? [];

        $overlays = [];
        foreach ($deviceComponents as $comp) {
            if (($comp['otvtype'] ?? null) === 'overlay') {
                $adjacencies = [];
                foreach ($deviceComponents as $adj) {
                    if (($adj['otvtype'] ?? null) === 'adjacency' && ($adj['index'] ?? null) === ($comp['index'] ?? null)) {
                        $adjNormal = ($adj['status'] ?? 0) == 0;
                        $adj['is_normal'] = $adjNormal;
                        $adj['item_class'] = $adjNormal ? '' : 'list-group-item-danger';
                        $adjacencies[] = $adj;
                    }
                }
                $isNormal = ($comp['status'] ?? 0) == 0;
                $comp['is_normal'] = $isNormal;
                $comp['item_class'] = $isNormal ? '' : 'list-group-item-danger';
                $comp['adjacencies'] = $adjacencies;
                $overlays[] = $comp;
            }
        }

        return [
            'overlays' => $overlays,
        ];
    }

    private function ospfData(Device $device, Request $request): array
    {
        $portCount = $device->ospfPorts()->count();
        $portCountEnabled = $device->ospfPorts()->where('ospfIfAdminStat', 'enabled')->count();
        $ports = $device->ospfPorts()
            ->with('port')
            ->where('ospfIfAdminStat', 'enabled')
            ->orderBy('ospfIfAreaId')
            ->get();

        $nbrs = $device->ospfNbrs()->get()->map(function ($nbr) {
            $host = \App\Models\Ipv4Address::where('ipv4_address', $nbr->ospfNbrRtrId)
                ->with('port.device')
                ->first()
                ?->port
                ?->device;

            return [
                'router_id' => $nbr->ospfNbrRtrId,
                'device' => $host,
                'ip_address' => $nbr->ospfNbrIpAddr,
                'state' => $nbr->ospfNbrState,
                'status_color' => match ($nbr->ospfNbrState) {
                    'full' => 'success',
                    'down' => 'danger',
                    default => 'default',
                },
            ];
        });

        $instances = [];
        foreach ($device->ospfInstances()->with('areas')->get() as $instance) {
            $areas = [];
            foreach ($instance->areas as $area) {
                $areaPortCount = $device->ospfPorts()->where('ospfIfAreaId', $area->ospfAreaId)->count();
                $areaPortCountEnabled = $device->ospfPorts()->where('ospfIfAdminStat', 'enabled')->where('ospfIfAreaId', $area->ospfAreaId)->count();

                $areas[] = [
                    'area_id' => $area->ospfAreaId,
                    'port_count' => $areaPortCount,
                    'port_count_enabled' => $areaPortCountEnabled,
                    'status' => $instance->ospfAdminStat,
                ];
            }

            $instances[] = [
                'instance' => $instance,
                'router_id' => $instance->ospfRouterId,
                'admin_stat' => $instance->ospfAdminStat,
                'status_color' => $instance->ospfAdminStat === 'enabled' ? 'success' : 'default',
                'abr_status' => $instance->ospfAreaBdrRtrStatus,
                'abr_status_color' => $instance->ospfAreaBdrRtrStatus === 'true' ? 'success' : 'default',
                'asbr_status' => $instance->ospfASBdrRtrStatus,
                'asbr_status_color' => $instance->ospfASBdrRtrStatus === 'true' ? 'success' : 'default',
                'area_count' => $instance->areas->count(),
                'port_count' => $portCount,
                'port_count_enabled' => $portCountEnabled,
                'nbr_count' => $nbrs->count(),
                'areas' => $areas,
                'ports' => $ports,
                'nbrs' => $nbrs,
            ];
        }

        return [
            'instances' => $instances,
        ];
    }

    private function ospfv3Data(Device $device, Request $request): array
    {
        $instances = $device->ospfv3Instances()
            ->with([
                'areas.ospfv3Ports',
                'nbrs.port',
                'ospfv3Ports.port',
            ])
            ->get()
            ->map(function ($instance) {
                $portCountEnabled = $instance->ospfv3Ports->where('ospfv3IfAdminStatus', 'enabled')->count();
                $statusColor = $instance->ospfv3AdminStatus === 'enabled' ? 'success' : 'default';
                $abrColor = $instance->ospfv3AreaBdrRtrStatus === 'true' ? 'success' : 'default';
                $asbrColor = $instance->ospfv3ASBdrRtrStatus === 'true' ? 'success' : 'default';

                $areas = $instance->areas->map(function ($area) use ($instance, $statusColor) {
                    return [
                        'area_id_ip' => long2ip($area->ospfv3AreaId),
                        'port_count' => $area->ospfv3Ports->count(),
                        'port_count_enabled' => $area->ospfv3Ports->where('ospfv3IfAdminStatus', 'enabled')->count(),
                        'lsa_count' => $area->ospfv3AreaScopeLsaCount,
                        'status' => $instance->ospfv3AdminStatus,
                        'status_color' => $statusColor,
                    ];
                });

                $ports = $instance->ospfv3Ports->map(function ($ospfPort) {
                    return [
                        'port' => $ospfPort->port,
                        'port_id' => $ospfPort->port_id,
                        'type' => $ospfPort->ospfv3IfType,
                        'state' => $ospfPort->ospfv3IfState,
                        'cost' => $ospfPort->ospfv3IfMetricValue,
                        'area_id_ip' => long2ip($ospfPort->ospfv3IfAreaId),
                    ];
                });

                $nbrs = $instance->nbrs->map(function ($nbr) {
                    return [
                        'router_id' => $nbr->router_id,
                        'device_id' => $nbr->port?->device_id,
                        'address' => $nbr->ospfv3NbrAddress,
                        'state' => $nbr->ospfv3NbrState,
                        'status_color' => match ($nbr->ospfv3NbrState) {
                            'full' => 'success',
                            'down' => 'danger',
                            default => 'default',
                        },
                    ];
                });

                return [
                    'router_id' => $instance->router_id,
                    'admin_status' => $instance->ospfv3AdminStatus,
                    'status_color' => $statusColor,
                    'abr_status' => $instance->ospfv3AreaBdrRtrStatus,
                    'abr_color' => $abrColor,
                    'asbr_status' => $instance->ospfv3ASBdrRtrStatus,
                    'asbr_color' => $asbrColor,
                    'area_count' => $instance->areas->count(),
                    'port_count' => $instance->ospfv3Ports->count(),
                    'port_count_enabled' => $portCountEnabled,
                    'nbr_count' => $instance->nbrs->count(),
                    'areas' => $areas,
                    'ports' => $ports,
                    'nbrs' => $nbrs,
                ];
            });

        return [
            'instances' => $instances,
        ];
    }

    private function isisData(Device $device, Request $request): array
    {
        $adjacencies = $device->isisAdjacencies()
            ->with('port')
            ->get()
            ->map(function ($adj) {
                return [
                    'port' => $adj->port,
                    'port_id' => $adj->port_id,
                    'ip_address' => $adj->isisISAdjIPAddrAddress,
                    'neighbour_sys_id' => $adj->isisISAdjNeighSysID,
                    'area_address' => $adj->isisISAdjAreaAddress,
                    'neighbour_sys_type' => $adj->isisISAdjNeighSysType,
                    'admin_state' => $adj->isisCircAdminState,
                    'state' => $adj->isisISAdjState,
                    'state_color' => $adj->isisISAdjState === 'up' ? 'success' : 'danger',
                    'last_uptime' => \LibreNMS\Util\Time::formatInterval($adj->isisISAdjLastUpTime),
                ];
            });

        return [
            'adjacencies' => $adjacencies,
        ];
    }

    private function routesData(Device $device, Request $request): array
    {
        return [
            'max_routes' => LibrenmsConfig::get('routes_max_number', 300),
        ];
    }

    private function bgpData(Device $device, Request $request): array
    {
        $view = (string) $request->query('view', 'basic');
        $validViews = [
            'basic',
            'updates',
            'prefixes_ipv4unicast',
            'prefixes_ipv4vpn',
            'prefixes_ipv6unicast',
            'prefixes_ipv6vpn',
            'macaccounting_bits',
            'macaccounting_pkts',
        ];
        if (! in_array($view, $validViews, true)) {
            $view = 'basic';
        }

        $query = $device->bgppeers();
        if ($view === 'prefixes_ipv4unicast') {
            $query->where('bgpPeerIdentifier', 'not like', '%:%');
        } elseif ($view === 'prefixes_ipv6unicast' || $view === 'prefixes_ipv6vpn') {
            $query->where('bgpPeerIdentifier', 'like', '%:%');
        }

        $peers = $query->orderBy('bgpPeerRemoteAs')
            ->orderBy('bgpPeerIdentifier')
            ->get()
            ->map(function ($peer) use ($device, $view) {
                $peerIdentifierIp = \LibreNMS\Util\IP::parse($peer->bgpPeerIdentifier, true);

                $peerType = 'eBGP';
                $peerTypeClass = 'text-success';
                if ($peer->bgpPeerRemoteAs == $device->bgpLocalAs) {
                    $peerType = 'iBGP';
                    $peerTypeClass = 'text-primary';
                } elseif (
                    ($peer->bgpPeerRemoteAs >= 64512 && $peer->bgpPeerRemoteAs <= 65534)
                    || ($peer->bgpPeerRemoteAs >= 4200000000 && $peer->bgpPeerRemoteAs <= 4294967294)
                ) {
                    $peerType = 'Priv eBGP';
                    $peerTypeClass = 'text-info';
                }

                $ipv4Host = \App\Models\Ipv4Address::where('ipv4_address', $peer->bgpPeerIdentifier)
                    ->with('port.device')
                    ->first()
                    ?->port;

                $ipv6Host = null;
                if ($peerIdentifierIp) {
                    $ipv6Host = \App\Models\Ipv6Address::where('ipv6_address', $peerIdentifierIp->uncompressed())
                        ->with('port.device')
                        ->first()
                        ?->port;
                }

                $linkedPort = $ipv4Host ?: $ipv6Host;

                $cbgpList = \App\Models\BgpPeerCbgp::where('device_id', $device->device_id)
                    ->where('bgpPeerIdentifier', $peer->bgpPeerIdentifier)
                    ->get();
                $afiList = $cbgpList->map(fn ($c) => $c->afi . '.' . $c->safi)->implode(', ');
                $afisafiMap = $cbgpList->mapWithKeys(fn ($c) => [$c->afi . $c->safi => true])->all();

                $lastError = '';
                if ($peer->bgpPeerLastErrorCode != 0 || $peer->bgpPeerLastErrorSubCode != 0) {
                    $lastError = describe_bgp_error_code($peer->bgpPeerLastErrorCode, $peer->bgpPeerLastErrorSubCode);
                }
                if ($peer->bgpPeerLastErrorText) {
                    $lastError = trim($lastError . ' ' . $peer->bgpPeerLastErrorText);
                }

                $showGraph = false;
                $graphType = 'bgp_' . $view;
                $graphId = $peer->bgpPeer_id;

                if ($view === 'updates') {
                    $showGraph = true;
                } elseif (str_starts_with($view, 'prefixes_')) {
                    [, $afisafi] = explode('_', $view);
                    if (! empty($afisafiMap[$afisafi])) {
                        $showGraph = true;
                    }
                } elseif ($view === 'macaccounting_bits' || $view === 'macaccounting_pkts') {
                    $macRow = DB::table('ipv4_mac')
                        ->join('mac_accounting', 'mac_accounting.mac', '=', 'ipv4_mac.mac_address')
                        ->join('ports', 'ports.port_id', '=', 'mac_accounting.port_id')
                        ->where('ipv4_mac.ipv4_address', $peer->bgpPeerIdentifier)
                        ->where('ports.device_id', $device->device_id)
                        ->select('mac_accounting.ma_id')
                        ->first();
                    if ($macRow) {
                        $showGraph = true;
                        $graphId = $macRow->ma_id;
                        $graphType = $view;
                    }
                }

                return [
                    'peer' => $peer,
                    'remote_as' => $peer->bgpPeerRemoteAs,
                    'astext' => $peer->astext,
                    'descr' => $peer->bgpPeerDescr,
                    'admin_status' => $peer->bgpPeerAdminStatus,
                    'state' => $peer->bgpPeerState,
                    'fsm_established_time' => \LibreNMS\Util\Time::formatInterval($peer->bgpPeerFsmEstablishedTime),
                    'in_updates' => $peer->bgpPeerInUpdates,
                    'out_updates' => $peer->bgpPeerOutUpdates,
                    'identifier_compressed' => $peerIdentifierIp?->compressed() ?: $peer->bgpPeerIdentifier,
                    'peer_type' => $peerType,
                    'peer_type_class' => $peerTypeClass,
                    'linked_port' => $linkedPort,
                    'afi_list' => $afiList,
                    'last_error' => $lastError,
                    'state_color' => $peer->bgpPeerState === 'established' ? 'success' : 'danger',
                    'admin_color' => in_array($peer->bgpPeerAdminStatus, ['start', 'running'], true) ? 'success' : 'default',
                    'show_graph' => $showGraph,
                    'graph_type' => $graphType,
                    'graph_id' => $graphId,
                ];
            });

        $bgpOptions = [
            'basic' => [
                'text' => __('Basic'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'bgp', 'view' => 'basic']),
            ],
            'updates' => [
                'text' => __('Updates'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'bgp', 'view' => 'updates']),
            ],
            'prefixes_ipv4unicast' => [
                'text' => __('IPv4 Ucast'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'bgp', 'view' => 'prefixes_ipv4unicast']),
            ],
            'prefixes_ipv4vpn' => [
                'text' => __('VPNv4 Ucast'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'bgp', 'view' => 'prefixes_ipv4vpn']),
            ],
            'prefixes_ipv6unicast' => [
                'text' => __('IPv6 Ucast'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'bgp', 'view' => 'prefixes_ipv6unicast']),
            ],
            'prefixes_ipv6vpn' => [
                'text' => __('VPNv6 Ucast'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'bgp', 'view' => 'prefixes_ipv6vpn']),
            ],
            'macaccounting_bits' => [
                'text' => __('Bits'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'bgp', 'view' => 'macaccounting_bits']),
            ],
            'macaccounting_pkts' => [
                'text' => __('Packets'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'bgp', 'view' => 'macaccounting_pkts']),
            ],
        ];

        return [
            'view' => $view,
            'local_as' => $device->bgpLocalAs,
            'bgp_options' => $bgpOptions,
            'peers' => $peers,
        ];
    }

    private function mplsData(Device $device, Request $request): array
    {
        $view = (string) $request->query('view', 'lsp');
        $validViews = ['lsp', 'paths', 'sdps', 'sdpbinds', 'services', 'saps'];
        if (! in_array($view, $validViews, true)) {
            $view = 'lsp';
        }

        $items = match ($view) {
            'lsp' => $device->mplsLsps()
                ->leftJoin('vrfs', function ($join) {
                    $join->on('vrfs.vrf_oid', '=', 'mpls_lsps.vrf_oid')
                        ->on('vrfs.device_id', '=', 'mpls_lsps.device_id');
                })
                ->select('mpls_lsps.*', 'vrfs.vrf_name')
                ->orderBy('mplsLspName')
                ->get()
                ->map(function ($lsp) {
                    $host = \App\Models\Ipv4Address::where('ipv4_address', $lsp->mplsLspToAddr)
                        ->with('port.device')
                        ->first()
                        ?->port
                        ?->device;

                    $adminStateColor = $lsp->mplsLspAdminState === 'inService' ? 'success' : 'default';
                    $operStateColor = match (true) {
                        $lsp->mplsLspOperState === 'inService' => 'success',
                        $lsp->mplsLspAdminState === 'inService' && $lsp->mplsLspOperState === 'outOfService' => 'danger',
                        default => 'default',
                    };

                    $pathStateColor = match (true) {
                        $lsp->mplsLspConfiguredPaths + $lsp->mplsLspStandbyPaths == $lsp->mplsLspOperationalPaths => 'success',
                        $lsp->mplsLspOperationalPaths == 0 => 'danger',
                        $lsp->mplsLspConfiguredPaths + $lsp->mplsLspStandbyPaths > $lsp->mplsLspOperationalPaths => 'warning',
                        default => 'default',
                    };

                    $avail = Number::calculatePercent($lsp->mplsLspPrimaryTimeUp, $lsp->mplsLspAge, 5);

                    return [
                        'lsp' => $lsp,
                        'name' => $lsp->mplsLspName,
                        'to_addr' => $lsp->mplsLspToAddr,
                        'vrf_name' => $lsp->vrf_name,
                        'admin_state' => $lsp->mplsLspAdminState,
                        'oper_state' => $lsp->mplsLspOperState,
                        'last_change' => \LibreNMS\Util\Time::formatInterval($lsp->mplsLspLastChange),
                        'transitions' => $lsp->mplsLspTransitions,
                        'last_transition' => \LibreNMS\Util\Time::formatInterval($lsp->mplsLspLastTransition),
                        'configured_paths' => $lsp->mplsLspConfiguredPaths,
                        'standby_paths' => $lsp->mplsLspStandbyPaths,
                        'operational_paths' => $lsp->mplsLspOperationalPaths,
                        'type' => $lsp->mplsLspType,
                        'fast_reroute' => $lsp->mplsLspFastReroute,
                        'destination_device' => $host,
                        'admin_color' => $adminStateColor,
                        'oper_color' => $operStateColor,
                        'path_color' => $pathStateColor,
                        'availability' => $avail,
                    ];
                }),

            'paths' => $device->mplsLspPaths()
                ->join('mpls_lsps', 'mpls_lsp_paths.lsp_id', '=', 'mpls_lsps.lsp_id')
                ->select('mpls_lsp_paths.*', 'mpls_lsps.mplsLspName')
                ->orderBy('mpls_lsps.mplsLspName')
                ->get()
                ->map(function ($path) {
                    $host = \App\Models\Ipv4Address::where('ipv4_address', $path->mplsLspPathFailNodeAddr)
                        ->with('port.device')
                        ->first()
                        ?->port
                        ?->device;

                    $adminStateColor = $path->mplsLspPathAdminState === 'inService' ? 'success' : 'default';
                    $operStateColor = match (true) {
                        $path->mplsLspPathOperState === 'inService' => 'success',
                        $path->mplsLspPathAdminState === 'inService' && $path->mplsLspPathOperState === 'outOfService' => 'danger',
                        default => 'default',
                    };
                    $failCodeColor = $path->mplsLspPathFailCode === 'noError' ? 'success' : 'warning';

                    return [
                        'path' => $path,
                        'name' => $path->mplsLspName,
                        'path_oid' => $path->path_oid,
                        'type' => $path->mplsLspPathType,
                        'admin_state' => $path->mplsLspPathAdminState,
                        'oper_state' => $path->mplsLspPathOperState,
                        'last_change' => \LibreNMS\Util\Time::formatInterval($path->mplsLspPathLastChange),
                        'transition_count' => $path->mplsLspPathTransitionCount,
                        'bandwidth' => $path->mplsLspPathBandwidth,
                        'oper_bandwidth' => $path->mplsLspPathOperBandwidth,
                        'state' => $path->mplsLspPathState,
                        'fail_code' => $path->mplsLspPathFailCode,
                        'fail_node_addr' => $path->mplsLspPathFailNodeAddr,
                        'metric' => $path->mplsLspPathMetric,
                        'oper_metric' => $path->mplsLspPathOperMetric,
                        'fail_node_device' => $host,
                        'admin_color' => $adminStateColor,
                        'oper_color' => $operStateColor,
                        'fail_code_color' => $failCodeColor,
                    ];
                }),

            'sdps' => $device->mplsSdps()
                ->orderBy('sdp_oid')
                ->get()
                ->map(function ($sdp) {
                    $host = \App\Models\Ipv4Address::where('ipv4_address', $sdp->sdpFarEndInetAddress)
                        ->with('port.device')
                        ->first()
                        ?->port
                        ?->device;

                    $adminColor = $sdp->sdpAdminStatus === 'up' ? 'success' : 'default';
                    $operColor = match (true) {
                        $sdp->sdpOperStatus === 'up' => 'success',
                        $sdp->sdpAdminStatus === 'up' && $sdp->sdpOperStatus === 'down' => 'danger',
                        default => 'default',
                    };

                    return [
                        'sdp' => $sdp,
                        'sdp_oid' => $sdp->sdp_oid,
                        'far_end_addr' => $sdp->sdpFarEndInetAddress,
                        'delivery' => $sdp->sdpDelivery,
                        'active_lsp_type' => $sdp->sdpActiveLspType,
                        'description' => $sdp->sdpDescription,
                        'admin_status' => $sdp->sdpAdminStatus,
                        'oper_status' => $sdp->sdpOperStatus,
                        'admin_path_mtu' => $sdp->sdpAdminPathMtu,
                        'oper_path_mtu' => $sdp->sdpOperPathMtu,
                        'last_mgmt_change' => \LibreNMS\Util\Time::formatInterval($sdp->sdpLastMgmtChange),
                        'last_status_change' => \LibreNMS\Util\Time::formatInterval($sdp->sdpLastStatusChange),
                        'destination_device' => $host,
                        'admin_color' => $adminColor,
                        'oper_color' => $operColor,
                    ];
                }),

            'sdpbinds' => $device->mplsSdpBinds()
                ->leftJoin('mpls_services', 'mpls_sdp_binds.svc_id', '=', 'mpls_services.svc_id')
                ->select('mpls_sdp_binds.*', 'mpls_services.svc_oid as svcId')
                ->orderBy('sdp_oid')
                ->orderBy('svc_oid')
                ->get()
                ->map(function ($sdpbind) {
                    $adminColor = $sdpbind->sdpBindAdminStatus === 'up' ? 'success' : 'default';
                    $operColor = match (true) {
                        $sdpbind->sdpBindAdminStatus === 'up' && $sdpbind->sdpBindOperStatus === 'up' => 'success',
                        $sdpbind->sdpBindAdminStatus === 'up' && $sdpbind->sdpBindOperStatus === 'down' => 'danger',
                        default => 'default',
                    };

                    return [
                        'sdpbind' => $sdpbind,
                        'svc_id' => $sdpbind->svcId,
                        'sdp_oid' => $sdpbind->sdp_oid,
                        'svc_oid' => $sdpbind->svc_oid,
                        'bind_type' => $sdpbind->sdpBindType,
                        'vc_type' => $sdpbind->sdpBindVcType,
                        'admin_status' => $sdpbind->sdpBindAdminStatus,
                        'oper_status' => $sdpbind->sdpBindOperStatus,
                        'last_mgmt_change' => \LibreNMS\Util\Time::formatInterval($sdpbind->sdpBindLastMgmtChange),
                        'last_status_change' => \LibreNMS\Util\Time::formatInterval($sdpbind->sdpLastStatusChange),
                        'ing_fwd_packets' => $sdpbind->sdpBindBaseStatsIngFwdPackets,
                        'ing_fwd_octets' => $sdpbind->sdpBindBaseStatsIngFwdOctets,
                        'egr_fwd_packets' => $sdpbind->sdpBindBaseStatsEgrFwdPackets,
                        'egr_fwd_octets' => $sdpbind->sdpBindBaseStatsEgrFwdOctets,
                        'admin_color' => $adminColor,
                        'oper_color' => $operColor,
                    ];
                }),

            'services' => $device->mplsServices()
                ->leftJoin('vrfs', function ($join) {
                    $join->on('mpls_services.svcVRouterId', '=', 'vrfs.vrf_oid')
                        ->on('mpls_services.device_id', '=', 'vrfs.device_id');
                })
                ->select('mpls_services.*', 'vrfs.vrf_name')
                ->orderBy('svc_oid')
                ->get()
                ->map(function ($svc) {
                    $adminColor = $svc->svcAdminStatus === 'up' ? 'success' : 'default';
                    $operColor = match (true) {
                        $svc->svcAdminStatus === 'up' && $svc->svcOperStatus === 'up' => 'success',
                        $svc->svcAdminStatus === 'up' && $svc->svcOperStatus === 'down' => 'danger',
                        default => 'default',
                    };

                    $fdbUsage = Number::calculatePercent($svc->svcTlsFdbNumEntries, $svc->svcTlsFdbTableSize);
                    $fdbColor = match (true) {
                        $fdbUsage > 95 => 'danger',
                        $fdbUsage > 75 => 'warning',
                        default => 'success',
                    };

                    return [
                        'service' => $svc,
                        'svc_oid' => $svc->svc_oid,
                        'type' => $svc->svcType,
                        'cust_id' => $svc->svcCustId,
                        'admin_status' => $svc->svcAdminStatus,
                        'oper_status' => $svc->svcOperStatus,
                        'description' => $svc->svcDescription,
                        'mtu' => $svc->svcMtu,
                        'num_saps' => $svc->svcNumSaps,
                        'last_mgmt_change' => \LibreNMS\Util\Time::formatInterval($svc->svcLastMgmtChange),
                        'last_status_change' => \LibreNMS\Util\Time::formatInterval($svc->svcLastStatusChange),
                        'vrf_name' => $svc->vrf_name,
                        'mac_learning' => $svc->svcTlsMacLearning,
                        'fdb_table_size' => $svc->svcTlsFdbTableSize,
                        'fdb_num_entries' => $svc->svcTlsFdbNumEntries,
                        'stp_admin_status' => $svc->svcTlsStpAdminStatus,
                        'stp_oper_status' => $svc->svcTlsStpOperStatus,
                        'admin_color' => $adminColor,
                        'oper_color' => $operColor,
                        'fdb_usage' => $fdbUsage,
                        'fdb_color' => $fdbColor,
                    ];
                }),

            'saps' => $device->mplsSaps()
                ->with('port')
                ->orderBy('svc_oid')
                ->orderBy('sapPortId')
                ->orderBy('sapEncapValue')
                ->get()
                ->map(function ($sap) {
                    $adminColor = $sap->sapAdminStatus === 'up' ? 'success' : 'default';
                    $operColor = match (true) {
                        $sap->sapAdminStatus === 'up' && $sap->sapOperStatus === 'up' => 'success',
                        $sap->sapAdminStatus === 'up' && $sap->sapOperStatus === 'down' => 'danger',
                        default => 'default',
                    };

                    return [
                        'sap' => $sap,
                        'svc_oid' => $sap->svc_oid,
                        'port' => $sap->port,
                        'port_id' => $sap->port_id,
                        'encap_value' => $sap->sapEncapValue,
                        'type' => $sap->sapType,
                        'description' => $sap->sapDescription,
                        'admin_status' => $sap->sapAdminStatus,
                        'oper_status' => $sap->sapOperStatus,
                        'last_mgmt_change' => \LibreNMS\Util\Time::formatInterval($sap->sapLastMgmtChange),
                        'last_oper_change' => \LibreNMS\Util\Time::formatInterval($sap->sapLastStatusChange),
                        'admin_color' => $adminColor,
                        'oper_color' => $operColor,
                    ];
                }),
        };

        $mplsOptions = [
            'lsp' => [
                'text' => __('LSPs'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'mpls', 'view' => 'lsp']),
            ],
            'paths' => [
                'text' => __('Paths'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'mpls', 'view' => 'paths']),
            ],
            'sdps' => [
                'text' => __('SDPs'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'mpls', 'view' => 'sdps']),
            ],
            'sdpbinds' => [
                'text' => __('SDP binds'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'mpls', 'view' => 'sdpbinds']),
            ],
            'services' => [
                'text' => __('Services'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'mpls', 'view' => 'services']),
            ],
            'saps' => [
                'text' => __('SAPs'),
                'link' => route('device', ['device' => $device, 'tab' => 'routing', 'proto' => 'mpls', 'view' => 'saps']),
            ],
        ];

        return [
            'view' => $view,
            'mpls_options' => $mplsOptions,
            'items' => $items,
        ];
    }
}

