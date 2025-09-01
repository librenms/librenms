<?php

/**
 * Mpls.php
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
 * @copyright  2019 Vitali Kari
 * @copyright  2019 Tony Murray
 * @author     Vitali Kari <vitali.kari@gmail.com>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\MplsDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\MplsPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class Mpls implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports', 'vrf'];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof MplsDiscovery;
    }

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param  OS  $os
     */
    public function discover(OS $os): void
    {
        if ($os instanceof MplsDiscovery) {
            ModuleModelObserver::observe(\App\Models\MplsLsp::class, 'MPLS LSPs');
            $lsps = $this->syncModels($os->getDevice(), 'mplsLsps', $os->discoverMplsLsps());
            ModuleModelObserver::done();

            ModuleModelObserver::observe(\App\Models\MplsLspPath::class, 'MPLS LSP Paths');
            $paths = $this->syncModels($os->getDevice(), 'mplsLspPaths', $os->discoverMplsPaths($lsps));
            ModuleModelObserver::done();

            ModuleModelObserver::observe(\App\Models\MplsSdp::class, 'MPLS SDPs');
            $sdps = $this->syncModels($os->getDevice(), 'mplsSdps', $os->discoverMplsSdps());
            ModuleModelObserver::done();

            ModuleModelObserver::observe(\App\Models\MplsService::class, 'MPLS Services');
            $svcs = $this->syncModels($os->getDevice(), 'mplsServices', $os->discoverMplsServices());
            ModuleModelObserver::done();

            ModuleModelObserver::observe(\App\Models\MplsSap::class, 'MPLS SAPs');
            $this->syncModels($os->getDevice(), 'mplsSaps', $os->discoverMplsSaps($svcs));
            ModuleModelObserver::done();

            ModuleModelObserver::observe(\App\Models\MplsSdpBind::class, 'MPLS SDP Bindings');
            $this->syncModels($os->getDevice(), 'mplsSdpBinds', $os->discoverMplsSdpBinds($sdps, $svcs));
            ModuleModelObserver::done();

            ModuleModelObserver::observe(\App\Models\MplsTunnelArHop::class, 'MPLS Tunnel Active Routing Hops');
            $this->syncModels($os->getDevice(), 'mplsTunnelArHops', $os->discoverMplsTunnelArHops($paths));
            ModuleModelObserver::done();

            ModuleModelObserver::observe(\App\Models\MplsTunnelCHop::class, 'MPLS Tunnel Constrained Shortest Path First Hops');
            $this->syncModels($os->getDevice(), 'mplsTunnelCHops', $os->discoverMplsTunnelCHops($paths));
            ModuleModelObserver::done();
        }
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof MplsPolling;
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param  OS  $os
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if ($os instanceof MplsPolling) {
            $device = $os->getDevice();

            if ($device->mplsLsps()->exists()) {
                ModuleModelObserver::observe(\App\Models\MplsLsp::class, 'MPLS LSPs');
                $lsps = $this->syncModels($device, 'mplsLsps', $os->pollMplsLsps());
            }

            if ($device->mplsLspPaths()->exists()) {
                ModuleModelObserver::observe(\App\Models\MplsLspPath::class, 'MPLS LSP Paths');
                $paths = $this->syncModels($device, 'mplsLspPaths', $os->pollMplsPaths($lsps));
                ModuleModelObserver::done();
            }

            if ($device->mplsSdps()->exists()) {
                ModuleModelObserver::observe(\App\Models\MplsSdp::class, 'MPLS SDPs');
                $sdps = $this->syncModels($device, 'mplsSdps', $os->pollMplsSdps());
                ModuleModelObserver::done();
            }

            if ($device->mplsServices()->exists()) {
                ModuleModelObserver::observe(\App\Models\MplsService::class, 'MPLS Services');
                $svcs = $this->syncModels($device, 'mplsServices', $os->pollMplsServices());
                ModuleModelObserver::done();
            }

            if ($device->mplsSaps()->exists() && isset($svcs)) {
                ModuleModelObserver::observe(\App\Models\MplsSap::class, 'MPLS SAPs');
                $this->syncModels($device, 'mplsSaps', $os->pollMplsSaps($svcs));
                ModuleModelObserver::done();
            }

            if ($device->mplsSdpBinds()->exists() && isset($sdps, $svcs)) {
                ModuleModelObserver::observe(\App\Models\MplsSdpBind::class, 'MPLS SDP Bindings');
                $this->syncModels($device, 'mplsSdpBinds', $os->pollMplsSdpBinds($sdps, $svcs));
                ModuleModelObserver::done();
            }

            if ($device->mplsTunnelArHops()->exists()) {
                ModuleModelObserver::observe(\App\Models\MplsTunnelArHop::class, 'MPLS Tunnel Active Routing Hops');
                $this->syncModels($device, 'mplsTunnelArHops', $os->pollMplsTunnelArHops($paths));
                ModuleModelObserver::done();
            }

            if ($device->mplsTunnelCHops()->exists()) {
                ModuleModelObserver::observe(\App\Models\MplsTunnelCHop::class, 'MPLS Tunnel Constrained Shortest Path First Hops');
                $this->syncModels($device, 'mplsTunnelCHops', $os->pollMplsTunnelCHops($paths));
                ModuleModelObserver::done();
            }
        }
    }

    public function dataExists(Device $device): bool
    {
        return $device->mplsLsps()->exists()
         || $device->mplsLspPaths()->exists()
         || $device->mplsSdps()->exists()
         || $device->mplsServices()->exists()
         || $device->mplsSaps()->exists()
         || $device->mplsSdpBinds()->exists()
         || $device->mplsTunnelArHops()->exists()
         || $device->mplsTunnelCHops()->exists();
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     */
    public function cleanup(Device $device): int
    {
        $deleted = $device->mplsLsps()->delete();
        $deleted += $device->mplsLspPaths()->delete();
        $deleted += $device->mplsSdps()->delete();
        $deleted += $device->mplsServices()->delete();
        $deleted += $device->mplsSaps()->delete();
        $deleted += $device->mplsSdpBinds()->delete();
        $deleted += $device->mplsTunnelArHops()->delete();
        $deleted += $device->mplsTunnelCHops()->delete();

        return $deleted;
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'mpls_lsps' => $device->mplsLsps()->orderBy('vrf_oid')->orderBy('lsp_oid')
                ->get()->map->makeHidden(['lsp_id', 'device_id']),
            'mpls_lsp_paths' => $device->mplsLspPaths()
                ->leftJoin('mpls_lsps', 'mpls_lsp_paths.lsp_id', 'mpls_lsps.lsp_id')
                ->select(['mpls_lsp_paths.*', 'mpls_lsps.vrf_oid', 'mpls_lsps.lsp_oid'])
                ->orderBy('vrf_oid')->orderBy('lsp_oid')->orderBy('path_oid')
                ->get()->map->makeHidden(['lsp_path_id', 'device_id', 'lsp_id']),
            'mpls_sdps' => $device->mplsSdps()->orderBy('sdp_oid')
                ->get()->map->makeHidden(['sdp_id', 'device_id']),
            'mpls_sdp_binds' => $device->mplsSdpBinds()
                ->leftJoin('mpls_sdps', 'mpls_sdp_binds.sdp_id', 'mpls_sdps.sdp_id')
                ->leftJoin('mpls_services', 'mpls_sdp_binds.svc_id', 'mpls_services.svc_id')
                ->orderBy('mpls_sdps.sdp_oid')->orderBy('mpls_services.svc_oid')
                ->select(['mpls_sdp_binds.*', 'mpls_sdps.sdp_oid', 'mpls_services.svc_oid'])
                ->get()->map->makeHidden(['bind_id', 'sdp_id', 'svc_id', 'device_id']),
            'mpls_services' => $device->mplsServices()->orderBy('svc_oid')
                ->get()->map->makeHidden(['svc_id', 'device_id']),
            'mpls_saps' => $device->mplsSaps()
                ->leftJoin('mpls_services', 'mpls_saps.svc_id', 'mpls_services.svc_id')
                ->orderBy('mpls_services.svc_oid')->orderBy('mpls_saps.sapPortId')->orderBy('mpls_saps.sapEncapValue')
                ->select(['mpls_saps.*', 'mpls_services.svc_oid'])
                ->get()->map->makeHidden(['sap_id', 'svc_id', 'device_id']),
        ];
    }
}
