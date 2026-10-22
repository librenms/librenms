<?php

/**
 * MplsTable.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\View\Components\Routing;

use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\MplsLsp;
use App\Models\MplsLspPath;
use App\Models\MplsSap;
use App\Models\MplsSdp;
use App\Models\MplsSdpBind;
use App\Models\MplsService;
use App\Models\Port;
use App\Models\Vrf;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use LibreNMS\Util\Number;
use LibreNMS\Util\Time;

/**
 * @phpstan-type Column array{label: string, title?: string}
 * @phpstan-type Row array<string, mixed>
 */
class MplsTable extends Component
{
    public const VIEWS = ['lsp', 'paths', 'sdps', 'sdpbinds', 'services', 'saps'];

    /** @var array<string, Column> */
    public array $columns;
    /** @var list<Row> */
    public array $rows;
    public string $empty;

    /** @var array<string, Device> */
    private array $addressDevices = [];
    /** @var array<string, string> */
    private array $vrfNames = [];

    /**
     * @param  EloquentCollection<int, MplsLsp|MplsLspPath|MplsSdp|MplsSdpBind|MplsService|MplsSap>  $items
     */
    public function __construct(
        public readonly string $view,
        EloquentCollection $items,
        public readonly bool $showDevice = false,
    ) {
        $items->loadMissing(array_filter([
            $showDevice ? 'device' : null,
            $view === 'paths' ? 'lsp' : null,
            $view === 'sdpbinds' ? 'service' : null,
        ]));

        $this->columns = $this->columns();
        $this->empty = $this->emptyText();
        $this->rows = match ($view) {
            'paths' => $this->pathRows($items->whereInstanceOf(MplsLspPath::class)),
            'sdps' => $this->sdpRows($items->whereInstanceOf(MplsSdp::class)),
            'sdpbinds' => $this->sdpBindRows($items->whereInstanceOf(MplsSdpBind::class)),
            'services' => $this->serviceRows($items->whereInstanceOf(MplsService::class)),
            'saps' => $this->sapRows($items->whereInstanceOf(MplsSap::class)),
            default => $this->lspRows($items->whereInstanceOf(MplsLsp::class)),
        };
    }

    /**
     * Option bar entries for each MPLS view
     *
     * @param  callable(string): string  $link
     * @return array<string, array{text: string, link: string}>
     */
    public static function options(callable $link): array
    {
        return [
            'lsp' => ['text' => __('LSPs'), 'link' => $link('lsp')],
            'paths' => ['text' => __('Paths'), 'link' => $link('paths')],
            'sdps' => ['text' => __('SDPs'), 'link' => $link('sdps')],
            'sdpbinds' => ['text' => __('SDP binds'), 'link' => $link('sdpbinds')],
            'services' => ['text' => __('Services'), 'link' => $link('services')],
            'saps' => ['text' => __('SAPs'), 'link' => $link('saps')],
        ];
    }

    public function render(): View|Closure|string
    {
        return view('components.routing.mpls-table');
    }

    /**
     * @param  Collection<int, MplsLsp>  $lsps
     * @return list<Row>
     */
    private function lspRows(Collection $lsps): array
    {
        $this->loadAddressDevices($lsps->pluck('mplsLspToAddr'));
        $this->loadVrfNames($lsps->pluck('device_id'));

        return $lsps->map(function (MplsLsp $lsp) {
            $configured = $lsp->mplsLspConfiguredPaths + $lsp->mplsLspStandbyPaths;
            if ($configured == $lsp->mplsLspOperationalPaths) {
                $pathColor = 'success';
            } elseif ($lsp->mplsLspOperationalPaths == 0) {
                $pathColor = 'danger';
            } elseif ($configured > $lsp->mplsLspOperationalPaths) {
                $pathColor = 'warning';
            } else {
                $pathColor = 'default';
            }

            return [
                'device' => $this->showDevice ? $lsp->device : null,
                'name' => $lsp->mplsLspName,
                'destination_device' => $this->addressDevices[$lsp->mplsLspToAddr] ?? null,
                'destination' => $lsp->mplsLspToAddr,
                'vrf_name' => $this->vrfNames["$lsp->device_id|$lsp->vrf_oid"] ?? null,
                'admin_state' => $lsp->mplsLspAdminState,
                'admin_color' => $lsp->mplsLspAdminState === 'inService' ? 'success' : 'default',
                'oper_state' => $lsp->mplsLspOperState,
                'oper_color' => $this->operColor($lsp->mplsLspAdminState, $lsp->mplsLspOperState, 'inService', 'outOfService', true),
                'last_change' => Time::formatInterval($lsp->mplsLspLastChange),
                'transitions' => $lsp->mplsLspTransitions,
                'last_transition' => Time::formatInterval($lsp->mplsLspLastTransition),
                'paths' => "$lsp->mplsLspConfiguredPaths / $lsp->mplsLspStandbyPaths / $lsp->mplsLspOperationalPaths",
                'path_color' => $pathColor,
                'type' => $lsp->mplsLspType,
                'fast_reroute' => $lsp->mplsLspFastReroute,
                'availability' => Number::calculatePercent($lsp->mplsLspPrimaryTimeUp, $lsp->mplsLspAge, 5),
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, MplsLspPath>  $paths
     * @return list<Row>
     */
    private function pathRows(Collection $paths): array
    {
        $this->loadAddressDevices($paths->pluck('mplsLspPathFailNodeAddr'));

        return $paths->map(fn (MplsLspPath $path) => [
            'device' => $this->showDevice ? $path->device : null,
            'name' => $path->lsp?->mplsLspName,
            'path_oid' => $path->path_oid,
            'type' => $path->mplsLspPathType,
            'admin_state' => $path->mplsLspPathAdminState,
            'admin_color' => $path->mplsLspPathAdminState === 'inService' ? 'success' : 'default',
            'oper_state' => $path->mplsLspPathOperState,
            'oper_color' => $this->operColor($path->mplsLspPathAdminState, $path->mplsLspPathOperState, 'inService', 'outOfService', true),
            'last_change' => Time::formatInterval($path->mplsLspPathLastChange),
            'transitions' => $path->mplsLspPathTransitionCount,
            'bandwidth' => $path->mplsLspPathBandwidth,
            'oper_bandwidth' => $path->mplsLspPathOperBandwidth,
            'state' => $path->mplsLspPathState,
            'fail_code' => $path->mplsLspPathFailCode,
            'fail_code_color' => $path->mplsLspPathFailCode === 'noError' ? 'success' : 'warning',
            'fail_node_device' => $this->addressDevices[$path->mplsLspPathFailNodeAddr] ?? null,
            'fail_node' => $path->mplsLspPathFailNodeAddr,
            'metric' => $path->mplsLspPathMetric,
            'oper_metric' => $path->mplsLspPathOperMetric,
        ])->values()->all();
    }

    /**
     * @param  Collection<int, MplsSdp>  $sdps
     * @return list<Row>
     */
    private function sdpRows(Collection $sdps): array
    {
        $this->loadAddressDevices($sdps->pluck('sdpFarEndInetAddress'));

        return $sdps->map(fn (MplsSdp $sdp) => [
            'device' => $this->showDevice ? $sdp->device : null,
            'sdp_oid' => $sdp->sdp_oid,
            'destination_device' => $this->addressDevices[$sdp->sdpFarEndInetAddress] ?? null,
            'destination' => $sdp->sdpFarEndInetAddress,
            'delivery' => $sdp->sdpDelivery,
            'active_lsp_type' => $sdp->sdpActiveLspType,
            'description' => $sdp->sdpDescription,
            'admin_status' => $sdp->sdpAdminStatus,
            'admin_color' => $sdp->sdpAdminStatus === 'up' ? 'success' : 'default',
            'oper_status' => $sdp->sdpOperStatus,
            'oper_color' => $this->operColor($sdp->sdpAdminStatus, $sdp->sdpOperStatus, 'up', 'down', true),
            'admin_path_mtu' => $sdp->sdpAdminPathMtu,
            'oper_path_mtu' => $sdp->sdpOperPathMtu,
            'last_mgmt_change' => Time::formatInterval($sdp->sdpLastMgmtChange),
            'last_status_change' => Time::formatInterval($sdp->sdpLastStatusChange),
        ])->values()->all();
    }

    /**
     * @param  Collection<int, MplsSdpBind>  $binds
     * @return list<Row>
     */
    private function sdpBindRows(Collection $binds): array
    {
        return $binds->map(fn (MplsSdpBind $bind) => [
            'device' => $this->showDevice ? $bind->device : null,
            'svc_oid' => $bind->service?->svc_oid,
            'bind_id' => "$bind->sdp_oid:$bind->svc_oid",
            'bind_type' => $bind->sdpBindType,
            'vc_type' => $bind->sdpBindVcType,
            'admin_status' => $bind->sdpBindAdminStatus,
            'admin_color' => $bind->sdpBindAdminStatus === 'up' ? 'success' : 'default',
            'oper_status' => $bind->sdpBindOperStatus,
            'oper_color' => $this->operColor($bind->sdpBindAdminStatus, $bind->sdpBindOperStatus, 'up', 'down'),
            'last_mgmt_change' => Time::formatInterval($bind->sdpBindLastMgmtChange),
            'last_status_change' => Time::formatInterval($bind->sdpBindLastStatusChange),
            'ing_fwd_packets' => $bind->sdpBindBaseStatsIngFwdPackets,
            'ing_fwd_octets' => $bind->sdpBindBaseStatsIngFwdOctets,
            'egr_fwd_packets' => $bind->sdpBindBaseStatsEgrFwdPackets,
            'egr_fwd_octets' => $bind->sdpBindBaseStatsEgrFwdOctets,
        ])->values()->all();
    }

    /**
     * @param  Collection<int, MplsService>  $services
     * @return list<Row>
     */
    private function serviceRows(Collection $services): array
    {
        $this->loadVrfNames($services->pluck('device_id'));

        return $services->map(function (MplsService $svc) {
            $fdbUsage = Number::calculatePercent($svc->svcTlsFdbNumEntries, $svc->svcTlsFdbTableSize);
            if ($fdbUsage > 95) {
                $fdbColor = 'danger';
            } elseif ($fdbUsage > 75) {
                $fdbColor = 'warning';
            } else {
                $fdbColor = 'success';
            }

            return [
                'device' => $this->showDevice ? $svc->device : null,
                'svc_oid' => $svc->svc_oid,
                'type' => $svc->svcType,
                'cust_id' => $svc->svcCustId,
                'admin_status' => $svc->svcAdminStatus,
                'admin_color' => $svc->svcAdminStatus === 'up' ? 'success' : 'default',
                'oper_status' => $svc->svcOperStatus,
                'oper_color' => $this->operColor($svc->svcAdminStatus, $svc->svcOperStatus, 'up', 'down'),
                'description' => $svc->svcDescription,
                'mtu' => $svc->svcMtu,
                'num_saps' => $svc->svcNumSaps,
                'last_mgmt_change' => Time::formatInterval($svc->svcLastMgmtChange),
                'last_status_change' => Time::formatInterval($svc->svcLastStatusChange),
                'vrf_name' => $this->vrfNames["$svc->device_id|$svc->svcVRouterId"] ?? null,
                'mac_learning' => $svc->svcTlsMacLearning,
                'fdb_table_size' => $svc->svcTlsFdbTableSize,
                'fdb_num_entries' => $svc->svcTlsFdbNumEntries,
                'fdb_color' => $fdbColor,
                'stp_admin_status' => $svc->svcTlsStpAdminStatus,
                'stp_oper_status' => $svc->svcTlsStpOperStatus,
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, MplsSap>  $saps
     * @return list<Row>
     */
    private function sapRows(Collection $saps): array
    {
        // the port relationship is not scoped to the device, so look up ports by device and ifName
        $ports = Port::query()
            ->whereIn('device_id', $saps->pluck('device_id')->unique())
            ->whereIn('ifName', $saps->pluck('ifName')->filter()->unique())
            ->get()
            ->keyBy(fn (Port $port) => "$port->device_id|$port->ifName");

        return $saps->map(fn (MplsSap $sap) => [
            'device' => $this->showDevice ? $sap->device : null,
            'svc_oid' => $sap->svc_oid,
            // QinQ wildcard saps are stored with encap 4095
            'graph_vars' => ['device' => $sap->device_id, 'traffic_id' => "$sap->svc_oid.$sap->sapPortId." . ($sap->sapEncapValue == '*' ? '4095' : $sap->sapEncapValue)],
            'port' => $ports->get("$sap->device_id|$sap->ifName"),
            'port_name' => $sap->ifName ?: $sap->sapPortId,
            'encap_value' => $sap->sapEncapValue,
            'type' => $sap->sapType,
            'description' => $sap->sapDescription,
            'admin_status' => $sap->sapAdminStatus,
            'admin_color' => $sap->sapAdminStatus === 'up' ? 'success' : 'default',
            'oper_status' => $sap->sapOperStatus,
            'oper_color' => $this->operColor($sap->sapAdminStatus, $sap->sapOperStatus, 'up', 'down'),
            'last_mgmt_change' => Time::formatInterval($sap->sapLastMgmtChange),
            'last_status_change' => Time::formatInterval($sap->sapLastStatusChange),
        ])->values()->all();
    }

    /**
     * Oper state is good if it is up, bad if it is down while admin up.
     * When $operUpIsEnough is false, admin must also be up for oper up to be good.
     */
    private function operColor(?string $admin, ?string $oper, string $up, string $down, bool $operUpIsEnough = false): string
    {
        if ($oper === $up && ($operUpIsEnough || $admin === $up)) {
            return 'success';
        }

        if ($admin === $up && $oper === $down) {
            return 'danger';
        }

        return 'default';
    }

    /**
     * Find devices that own the given IPv4 addresses
     *
     * @param  Collection<int, string|null>  $addresses
     */
    private function loadAddressDevices(Collection $addresses): void
    {
        $addresses = $addresses->filter()->unique();

        if ($addresses->isEmpty()) {
            return;
        }

        $this->addressDevices = Ipv4Address::whereIn('ipv4_address', $addresses)
            ->with('port.device')
            ->get()
            ->filter(fn (Ipv4Address $ip) => $ip->port?->device !== null)
            ->mapWithKeys(fn (Ipv4Address $ip) => [$ip->ipv4_address => $ip->port->device])
            ->all();
    }

    /**
     * The vrf relationships require a join, so look up names by device and vrf_oid instead
     *
     * @param  Collection<int, int>  $deviceIds
     */
    private function loadVrfNames(Collection $deviceIds): void
    {
        if ($deviceIds->isEmpty()) {
            return;
        }

        $this->vrfNames = Vrf::query()
            ->whereIn('device_id', $deviceIds->unique())
            ->get(['device_id', 'vrf_oid', 'vrf_name'])
            ->mapWithKeys(fn (Vrf $vrf) => ["$vrf->device_id|$vrf->vrf_oid" => (string) $vrf->vrf_name])
            ->all();
    }

    private function emptyText(): string
    {
        return match ($this->view) {
            'paths' => __('No MPLS LSP paths found.'),
            'sdps' => __('No MPLS SDPs found.'),
            'sdpbinds' => __('No MPLS SDP binds found.'),
            'services' => __('No MPLS services found.'),
            'saps' => __('No MPLS SAPs found.'),
            default => __('No MPLS LSPs found.'),
        };
    }

    /**
     * @return array<string, Column>
     */
    private function columns(): array
    {
        $columns = $this->showDevice ? ['device' => ['label' => __('Device')]] : [];

        return $columns + match ($this->view) {
            'paths' => [
                'name' => ['label' => __('LSP Name'), 'title' => __('Administrative name for LSP this path belongs to')],
                'path_oid' => ['label' => __('Index'), 'title' => __('The OID index of this path')],
                'type' => ['label' => __('Type'), 'title' => __('The role this path is taking within this LSP')],
                'admin_state' => ['label' => __('Admin State'), 'title' => __('The desired administrative state for this LSP Path')],
                'oper_state' => ['label' => __('Oper State'), 'title' => __('The current operational state of this LSP Path')],
                'last_change' => ['label' => __('Last Change'), 'title' => __('The sysUpTime when this LSP Path was last modified')],
                'transitions' => ['label' => __('Transitions'), 'title' => __('The number of transitions that have occurred for this LSP')],
                'bandwidth' => ['label' => __('Bandwidth'), 'title' => __('The amount of bandwidth in Mbps reserved for this LSP path. Zero indicates that no bandwidth is reserved')],
                'oper_bandwidth' => ['label' => __('Oper BW'), 'title' => __('The operational bandwidth of this LSP path')],
                'state' => ['label' => __('State'), 'title' => __('The current working state of this path within this LSP')],
                'fail_code' => ['label' => __('Failcode'), 'title' => __('The reason code for LSP path failure')],
                'fail_node' => ['label' => __('Fail Node'), 'title' => __('The node in the LSP path at which the LSP path failed')],
                'metric' => ['label' => __('Metric'), 'title' => __('The cost of the traffic engineered path returned by the IGP')],
                'oper_metric' => ['label' => __('Oper Metric'), 'title' => __('The operational metric for the LSP path')],
            ],
            'sdps' => [
                'sdp_oid' => ['label' => __('SDP Id'), 'title' => __('Service Distribution Point identifier')],
                'destination' => ['label' => __('Destination'), 'title' => __('The remote end of the tunnel defined by this SDP')],
                'delivery' => ['label' => __('Type'), 'title' => __('The type of delivery used by this SDP')],
                'active_lsp_type' => ['label' => __('LSP Type'), 'title' => __('The type of LSP that is currently active on this SDP')],
                'description' => ['label' => __('Description')],
                'admin_status' => ['label' => __('Admin State'), 'title' => __('The desired administrative state for this SDP')],
                'oper_status' => ['label' => __('Oper State'), 'title' => __('The current operational state of this SDP')],
                'admin_path_mtu' => ['label' => __('Admin MTU'), 'title' => __('The desired largest service frame size (in octets) that can be transmitted through this SDP. Zero means the path MTU is computed dynamically')],
                'oper_path_mtu' => ['label' => __('Oper MTU'), 'title' => __('The actual largest service frame size (in octets) that can be transmitted through this SDP')],
                'last_mgmt_change' => ['label' => __('Last Mgmt Change'), 'title' => __('The sysUpTime of the most recent management-initiated change to this SDP')],
                'last_status_change' => ['label' => __('Last Status Change'), 'title' => __('The sysUpTime of the most recent operating status change to this SDP')],
            ],
            'sdpbinds' => [
                'svc_oid' => ['label' => __('Service ID')],
                'bind_id' => ['label' => __('SDP Bind Id'), 'title' => __('SDP identifier : Service identifier')],
                'bind_type' => ['label' => __('Bind Type'), 'title' => __('Whether this Service SDP binding is a spoke or a mesh')],
                'vc_type' => ['label' => __('VC Type'), 'title' => __('The type of virtual circuit (VC) associated with the SDP binding')],
                'admin_status' => ['label' => __('Admin State'), 'title' => __('The desired state of this Service-SDP binding')],
                'oper_status' => ['label' => __('Oper State'), 'title' => __('The operating status of this Service-SDP binding')],
                'last_mgmt_change' => ['label' => __('Last Mgmt Change'), 'title' => __('The sysUpTime of the most recent management-initiated change to this Service-SDP binding')],
                'last_status_change' => ['label' => __('Last Status Change'), 'title' => __('The sysUpTime of the most recent operating status change to this SDP Bind')],
                'ing_fwd_packets' => ['label' => __('Ing Fwd Packets')],
                'ing_fwd_octets' => ['label' => __('Ing Fwd Octets')],
                'egr_fwd_packets' => ['label' => __('Egr Fwd Packets')],
                'egr_fwd_octets' => ['label' => __('Egr Fwd Octets')],
            ],
            'services' => [
                'svc_oid' => ['label' => __('Service ID')],
                'type' => ['label' => __('Type'), 'title' => __('The service type: e.g. epipe, tls, etc.')],
                'cust_id' => ['label' => __('Customer'), 'title' => __('The ID of the customer who owns this service')],
                'admin_status' => ['label' => __('Admin Status'), 'title' => __('The desired state of this service')],
                'oper_status' => ['label' => __('Oper Status'), 'title' => __('The operating state of this service')],
                'description' => ['label' => __('Description')],
                'mtu' => ['label' => __('Service MTU'), 'title' => __('The largest frame size (in octets) that this service can handle')],
                'num_saps' => ['label' => __('Num SAPs'), 'title' => __('The number of SAPs defined on this service')],
                'last_mgmt_change' => ['label' => __('Last Mgmt Change'), 'title' => __('The sysUpTime of the most recent management-initiated change to this service')],
                'last_status_change' => ['label' => __('Last Status Change'), 'title' => __('The sysUpTime of the most recent operating status change to this service')],
                'vrf_name' => ['label' => __('VRF'), 'title' => __('The virtual router instance associated with this IES or VPRN service')],
                'mac_learning' => ['label' => __('MAC Learning'), 'title' => __('Whether the MAC learning process is enabled in this TLS')],
                'fdb_table_size' => ['label' => __('FDB Table Size'), 'title' => __('The maximum number of learned and static entries allowed in the FDB of this service')],
                'fdb_num_entries' => ['label' => __('FDB Entries'), 'title' => __('The current number of entries allocated in the FDB of this service')],
                'stp_admin_status' => ['label' => __('STP Admin Status')],
                'stp_oper_status' => ['label' => __('STP Oper Status')],
            ],
            'saps' => [
                'svc_oid' => ['label' => __('Service ID')],
                'port' => ['label' => __('SAP Port'), 'title' => __('The access port where this SAP is defined')],
                'encap_value' => ['label' => __('Encapsulation'), 'title' => __('The label used to identify this SAP on the access port')],
                'type' => ['label' => __('Type'), 'title' => __('The type of service where this SAP is defined')],
                'description' => ['label' => __('Description')],
                'admin_status' => ['label' => __('Admin Status'), 'title' => __('The desired state of this SAP')],
                'oper_status' => ['label' => __('Oper Status'), 'title' => __('The operating state of this SAP')],
                'last_mgmt_change' => ['label' => __('Last Mgmt Change'), 'title' => __('The sysUpTime of the most recent management-initiated change to this SAP')],
                'last_status_change' => ['label' => __('Last Oper Change'), 'title' => __('The sysUpTime of the most recent operating status change to this SAP')],
            ],
            default => [
                'name' => ['label' => __('Name'), 'title' => __('Administrative name for this Labeled Switch Path')],
                'destination' => ['label' => __('Destination'), 'title' => __('The destination address of this LSP')],
                'vrf_name' => ['label' => __('VRF'), 'title' => __('Virtual Routing Instance')],
                'admin_state' => ['label' => __('Admin State'), 'title' => __('The desired administrative state for this LSP')],
                'oper_state' => ['label' => __('Oper State'), 'title' => __('The current operational state of this LSP')],
                'last_change' => ['label' => __('Last Change'), 'title' => __('The sysUpTime when this LSP was last modified')],
                'transitions' => ['label' => __('Transitions'), 'title' => __('The number of state transitions this LSP has undergone')],
                'last_transition' => ['label' => __('Last Transition'), 'title' => __('The time since the last transition occurred on this LSP')],
                'paths' => ['label' => __('Paths (Conf / Stby / Oper)'), 'title' => __('The number of configured, standby and operational paths for this LSP')],
                'type' => ['label' => __('Type'), 'title' => __('Whether the label value is statically or dynamically assigned or whether the LSP is used exclusively for bypass protection')],
                'fast_reroute' => ['label' => __('FRR'), 'title' => __('Whether fast reroute is enabled')],
                'availability' => ['label' => __('Availability %'), 'title' => __('LSP up time / LSP age * 100 %')],
            ],
        };
    }
}
