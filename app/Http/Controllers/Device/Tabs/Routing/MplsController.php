<?php

/**
 * MplsController.php
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

namespace App\Http\Controllers\Device\Tabs\Routing;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Ipv4Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use LibreNMS\Util\Number;

class MplsController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        $validViews = [
            'lsp',
            'paths',
            'sdps',
            'sdpbinds',
            'services',
            'saps',
        ];

        Validator::validate($request->all(), [
            'view' => 'nullable|in:' . implode(',', $validViews),
        ]);

        $view = (string) $request->query('view', 'lsp');

        $mplsOptions = [
            'lsp' => [
                'text' => __('LSPs'),
                'link' => route('device.routing.mpls', ['device' => $device, 'view' => 'lsp']),
            ],
            'paths' => [
                'text' => __('Paths'),
                'link' => route('device.routing.mpls', ['device' => $device, 'view' => 'paths']),
            ],
            'sdps' => [
                'text' => __('SDPs'),
                'link' => route('device.routing.mpls', ['device' => $device, 'view' => 'sdps']),
            ],
            'sdpbinds' => [
                'text' => __('SDP binds'),
                'link' => route('device.routing.mpls', ['device' => $device, 'view' => 'sdpbinds']),
            ],
            'services' => [
                'text' => __('Services'),
                'link' => route('device.routing.mpls', ['device' => $device, 'view' => 'services']),
            ],
            'saps' => [
                'text' => __('SAPs'),
                'link' => route('device.routing.mpls', ['device' => $device, 'view' => 'saps']),
            ],
        ];

        return view('device.tabs.routing.mpls', [
            'device' => $device,
            'view' => $view,
            'mpls_options' => $mplsOptions,
            'items' => match ($view) {
                'paths' => $this->getPaths($device),
                'sdps' => $this->getSdps($device),
                'sdpbinds' => $this->getSdpBinds($device),
                'services' => $this->getServices($device),
                'saps' => $this->getSaps($device),
                default => $this->getLsps($device),
            },
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getLsps(Device $device): array
    {
        $lsps = $device->mplsLsps()
            ->leftJoin('vrfs', function ($join): void {
                $join->on('vrfs.vrf_oid', '=', 'mpls_lsps.vrf_oid')
                    ->on('vrfs.device_id', '=', 'mpls_lsps.device_id');
            })
            ->select('mpls_lsps.*', 'vrfs.vrf_name')
            ->orderBy('mplsLspName')
            ->get();

        $items = [];
        foreach ($lsps as $lsp) {
            $host = Ipv4Address::where('ipv4_address', $lsp->mplsLspToAddr)
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

            $items[] = [
                'lsp' => $lsp,
                'name' => $lsp->mplsLspName,
                'destination_device' => $host,
                'to_addr' => $lsp->mplsLspToAddr,
                'vrf_name' => $lsp->vrf_name,
                'admin_state' => $lsp->mplsLspAdminState,
                'admin_color' => $adminStateColor,
                'oper_state' => $lsp->mplsLspOperState,
                'oper_color' => $operStateColor,
                'last_change' => $lsp->mplsLspLastChange,
                'transitions' => $lsp->mplsLspTransitions,
                'last_transition' => $lsp->mplsLspLastTransition,
                'configured_paths' => $lsp->mplsLspConfiguredPaths,
                'standby_paths' => $lsp->mplsLspStandbyPaths,
                'operational_paths' => $lsp->mplsLspOperationalPaths,
                'path_color' => $pathStateColor,
                'type' => $lsp->mplsLspType,
                'fast_reroute' => $lsp->mplsLspFastReroute,
                'availability' => $avail,
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getPaths(Device $device): array
    {
        $paths = $device->mplsLspPaths()
            ->join('mpls_lsps', 'mpls_lsp_paths.lsp_id', '=', 'mpls_lsps.lsp_id')
            ->select('mpls_lsp_paths.*', 'mpls_lsps.mplsLspName')
            ->orderBy('mpls_lsps.mplsLspName')
            ->get();

        $items = [];
        foreach ($paths as $path) {
            $host = Ipv4Address::where('ipv4_address', $path->mplsLspPathFailNodeAddr)
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

            $items[] = [
                'path' => $path,
                'name' => $path->mplsLspName ?? $path->lsp?->mplsLspName,
                'path_oid' => $path->path_oid,
                'type' => $path->mplsLspPathType,
                'admin_state' => $path->mplsLspPathAdminState,
                'admin_color' => $adminStateColor,
                'oper_state' => $path->mplsLspPathOperState,
                'oper_color' => $operStateColor,
                'last_change' => $path->mplsLspPathLastChange,
                'transition_count' => $path->mplsLspPathTransitionCount,
                'bandwidth' => $path->mplsLspPathBandwidth,
                'oper_bandwidth' => $path->mplsLspPathOperBandwidth,
                'state' => $path->mplsLspPathState,
                'fail_code' => $path->mplsLspPathFailCode,
                'fail_code_color' => $failCodeColor,
                'fail_node_device' => $host,
                'fail_node_addr' => $path->mplsLspPathFailNodeAddr,
                'metric' => $path->mplsLspPathMetric,
                'oper_metric' => $path->mplsLspPathOperMetric,
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSdps(Device $device): array
    {
        $sdps = $device->mplsSdps()
            ->orderBy('sdp_oid')
            ->get();

        $items = [];
        foreach ($sdps as $sdp) {
            $host = Ipv4Address::where('ipv4_address', $sdp->sdpFarEndInetAddress)
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

            $items[] = [
                'sdp' => $sdp,
                'sdp_oid' => $sdp->sdp_oid,
                'destination_device' => $host,
                'far_end_addr' => $sdp->sdpFarEndInetAddress,
                'delivery' => $sdp->sdpDelivery,
                'active_lsp_type' => $sdp->sdpActiveLspType,
                'description' => $sdp->sdpDescription,
                'admin_status' => $sdp->sdpAdminStatus,
                'admin_color' => $adminColor,
                'oper_status' => $sdp->sdpOperStatus,
                'oper_color' => $operColor,
                'admin_path_mtu' => $sdp->sdpAdminPathMtu,
                'oper_path_mtu' => $sdp->sdpOperPathMtu,
                'last_mgmt_change' => $sdp->sdpLastMgmtChange,
                'last_status_change' => $sdp->sdpLastStatusChange,
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSdpBinds(Device $device): array
    {
        $sdpbinds = $device->mplsSdpBinds()
            ->leftJoin('mpls_services', 'mpls_sdp_binds.svc_id', '=', 'mpls_services.svc_id')
            ->select('mpls_sdp_binds.*', 'mpls_services.svc_oid as svcId')
            ->orderBy('sdp_oid')
            ->orderBy('svc_oid')
            ->get();

        $items = [];
        foreach ($sdpbinds as $sdpbind) {
            $adminColor = $sdpbind->sdpBindAdminStatus === 'up' ? 'success' : 'default';
            $operColor = match (true) {
                $sdpbind->sdpBindAdminStatus === 'up' && $sdpbind->sdpBindOperStatus === 'up' => 'success',
                $sdpbind->sdpBindAdminStatus === 'up' && $sdpbind->sdpBindOperStatus === 'down' => 'danger',
                default => 'default',
            };

            $items[] = [
                'sdpbind' => $sdpbind,
                'svc_id' => $sdpbind->svc_id,
                'sdp_oid' => $sdpbind->sdp_oid,
                'svc_oid' => $sdpbind->svc_oid,
                'bind_type' => $sdpbind->sdpBindType,
                'vc_type' => $sdpbind->sdpBindVcType,
                'admin_status' => $sdpbind->sdpBindAdminStatus,
                'admin_color' => $adminColor,
                'oper_status' => $sdpbind->sdpBindOperStatus,
                'oper_color' => $operColor,
                'last_mgmt_change' => $sdpbind->sdpBindLastMgmtChange,
                'last_status_change' => $sdpbind->sdpBindLastStatusChange,
                'ing_fwd_packets' => $sdpbind->sdpBindBaseStatsIngFwdPackets,
                'ing_fwd_octets' => $sdpbind->sdpBindBaseStatsIngFwdOctets,
                'egr_fwd_packets' => $sdpbind->sdpBindBaseStatsEgrFwdPackets,
                'egr_fwd_octets' => $sdpbind->sdpBindBaseStatsEgrFwdOctets,
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getServices(Device $device): array
    {
        $services = $device->mplsServices()
            ->leftJoin('vrfs', function ($join): void {
                $join->on('mpls_services.svcVRouterId', '=', 'vrfs.vrf_oid')
                    ->on('mpls_services.device_id', '=', 'vrfs.device_id');
            })
            ->select('mpls_services.*', 'vrfs.vrf_name')
            ->orderBy('svc_oid')
            ->get();

        $items = [];
        foreach ($services as $svc) {
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

            $items[] = [
                'service' => $svc,
                'svc_oid' => $svc->svc_oid,
                'type' => $svc->svcType,
                'cust_id' => $svc->svcCustId,
                'admin_status' => $svc->svcAdminStatus,
                'admin_color' => $adminColor,
                'oper_status' => $svc->svcOperStatus,
                'oper_color' => $operColor,
                'description' => $svc->svcDescription,
                'mtu' => $svc->svcMtu,
                'num_saps' => $svc->svcNumSaps,
                'last_mgmt_change' => $svc->svcLastMgmtChange,
                'last_status_change' => $svc->svcLastStatusChange,
                'vrf_name' => $svc->vrf_name,
                'mac_learning' => $svc->svcTlsMacLearning,
                'fdb_table_size' => $svc->svcTlsFdbTableSize,
                'fdb_num_entries' => $svc->svcTlsFdbNumEntries,
                'fdb_color' => $fdbColor,
                'stp_admin_status' => $svc->svcTlsStpAdminStatus,
                'stp_oper_status' => $svc->svcTlsStpOperStatus,
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSaps(Device $device): array
    {
        $saps = $device->mplsSaps()
            ->with('port')
            ->orderBy('svc_oid')
            ->orderBy('sapPortId')
            ->orderBy('sapEncapValue')
            ->get();

        $items = [];
        foreach ($saps as $sap) {
            $adminColor = $sap->sapAdminStatus === 'up' ? 'success' : 'default';
            $operColor = match (true) {
                $sap->sapAdminStatus === 'up' && $sap->sapOperStatus === 'up' => 'success',
                $sap->sapAdminStatus === 'up' && $sap->sapOperStatus === 'down' => 'danger',
                default => 'default',
            };

            $items[] = [
                'sap' => $sap,
                'svc_oid' => $sap->svc_oid,
                'port' => $sap->port,
                'port_id' => $sap->port ? $sap->port->port_id : $sap->sapPortId,
                'encap_value' => $sap->sapEncapValue,
                'type' => $sap->sapType,
                'description' => $sap->sapDescription,
                'admin_status' => $sap->sapAdminStatus,
                'oper_status' => $sap->sapOperStatus,
                'last_mgmt_change' => $sap->sapLastMgmtChange,
                'last_oper_change' => $sap->sapLastStatusChange,
                'admin_color' => $adminColor,
                'oper_color' => $operColor,
            ];
        }

        return $items;
    }
}
