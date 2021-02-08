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
 * @copyright  2019 Vitali Kari
 * @copyright  2019 Tony Murray
 * @author     Vitali Kari <vitali.kari@gmail.com>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Discovery\MplsDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\MplsPolling;
use LibreNMS\OS;

class Mpls implements Module
{
    use SyncsModels;

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param OS $os
     */
    public function discover(OS $os)
    {
        if ($os instanceof MplsDiscovery) {
            echo "\nMPLS LSPs: ";
            ModuleModelObserver::observe('\App\Models\MplsLsp');
            $lsps = $this->syncModels($os->getDevice(), 'mplsLsps', $os->discoverMplsLsps());

            echo "\nMPLS LSP Paths: ";
            ModuleModelObserver::observe('\App\Models\MplsLspPath');
            $paths = $this->syncModels($os->getDevice(), 'mplsLspPaths', $os->discoverMplsPaths($lsps));

            echo "\nMPLS SDPs: ";
            ModuleModelObserver::observe('\App\Models\MplsSdp');
            $sdps = $this->syncModels($os->getDevice(), 'mplsSdps', $os->discoverMplsSdps());

            echo "\nMPLS Services: ";
            ModuleModelObserver::observe('\App\Models\MplsService');
            $svcs = $this->syncModels($os->getDevice(), 'mplsServices', $os->discoverMplsServices());

            echo "\nMPLS SAPs: ";
            ModuleModelObserver::observe('\App\Models\MplsSap');
            $this->syncModels($os->getDevice(), 'mplsSaps', $os->discoverMplsSaps($svcs));

            echo "\nMPLS SDP Bindings: ";
            ModuleModelObserver::observe('\App\Models\MplsSdpBind');
            $this->syncModels($os->getDevice(), 'mplsSdpBinds', $os->discoverMplsSdpBinds($sdps, $svcs));

            echo "\nMPLS Tunnel Active Routing Hops: ";
            ModuleModelObserver::observe('\App\Models\MplsTunnelArHop');
            $this->syncModels($os->getDevice(), 'mplsTunnelArHops', $os->discoverMplsTunnelArHops($paths));

            echo "\nMPLS Tunnel Constrained Shortest Path First Hops: ";
            ModuleModelObserver::observe('\App\Models\MplsTunnelCHop');
            $this->syncModels($os->getDevice(), 'mplsTunnelCHops', $os->discoverMplsTunnelCHops($paths));

            echo PHP_EOL;
        }
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param OS $os
     */
    public function poll(OS $os)
    {
        if ($os instanceof MplsPolling) {
            $device = $os->getDevice();

            if ($device->mplsLsps()->exists()) {
                echo "\nMPLS LSPs: ";
                ModuleModelObserver::observe('\App\Models\MplsLsp');
                $lsps = $this->syncModels($device, 'mplsLsps', $os->pollMplsLsps());
            }

            if ($device->mplsLspPaths()->exists()) {
                echo "\nMPLS LSP Paths: ";
                ModuleModelObserver::observe('\App\Models\MplsLspPath');
                $paths = $this->syncModels($device, 'mplsLspPaths', $os->pollMplsPaths($lsps));
            }

            if ($device->mplsSdps()->exists()) {
                echo "\nMPLS SDPs: ";
                ModuleModelObserver::observe('\App\Models\MplsSdp');
                $sdps = $this->syncModels($device, 'mplsSdps', $os->pollMplsSdps());
            }

            if ($device->mplsServices()->exists()) {
                echo "\nMPLS Services: ";
                ModuleModelObserver::observe('\App\Models\MplsService');
                $svcs = $this->syncModels($device, 'mplsServices', $os->pollMplsServices());
            }

            if ($device->mplsSaps()->exists()) {
                echo "\nMPLS SAPs: ";
                ModuleModelObserver::observe('\App\Models\MplsSap');
                $this->syncModels($device, 'mplsSaps', $os->pollMplsSaps($svcs));
            }

            if ($device->mplsSdpBinds()->exists()) {
                echo "\nMPLS SDP Bindings: ";
                ModuleModelObserver::observe('\App\Models\MplsSdpBind');
                $this->syncModels($device, 'mplsSdpBinds', $os->pollMplsSdpBinds($sdps, $svcs));
            }

            if ($device->mplsTunnelArHops()->exists()) {
                echo "\nMPLS Tunnel Active Routing Hops: ";
                ModuleModelObserver::observe('\App\Models\MplsTunnelArHop');
                $this->syncModels($device, 'mplsTunnelArHops', $os->pollMplsTunnelArHops($paths));
            }

            if ($device->mplsTunnelCHops()->exists()) {
                echo "\nMPLS Tunnel Constrained Shortest Path First Hops: ";
                ModuleModelObserver::observe('\App\Models\MplsTunnelCHop');
                $this->syncModels($device, 'mplsTunnelCHops', $os->pollMplsTunnelCHops($paths));
            }

            echo PHP_EOL;
        }
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     *
     * @param OS $os
     */
    public function cleanup(OS $os)
    {
        $os->getDevice()->mplsLsps()->delete();
        $os->getDevice()->mplsLspPaths()->delete();
        $os->getDevice()->mplsSdps()->delete();
        $os->getDevice()->mplsServices()->delete();
        $os->getDevice()->mplsSaps()->delete();
        $os->getDevice()->mplsSdpBinds()->delete();
        $os->getDevice()->mplsTunnelArHops()->delete();
        $os->getDevice()->mplsTunnelCHops()->delete();
    }
}
