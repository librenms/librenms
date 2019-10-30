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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Vitali Kari
 * @copyright  2019 Tony Murray
 * @author     Vitali Kari <vitali.kari@gmail.com>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Discovery\MplsDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\MplsPolling;
use LibreNMS\OS;
use LibreNMS\Util\ModuleModelObserver;

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
            $lsps = $this->syncModels($os->getDeviceModel(), 'mplsLsps', $os->discoverMplsLsps());

            echo "\nMPLS LSP Paths: ";
            ModuleModelObserver::observe('\App\Models\MplsLspPath');
            $this->syncModels($os->getDeviceModel(), 'mplsLspPaths', $os->discoverMplsPaths($lsps));

            echo "\nMPLS SDPs: ";
            ModuleModelObserver::observe('\App\Models\MplsSdp');
            $sdps = $this->syncModels($os->getDeviceModel(), 'mplsSdps', $os->discoverMplsSdps());

            echo "\nMPLS Services: ";
            ModuleModelObserver::observe('\App\Models\MplsService');
            $svcs = $this->syncModels($os->getDeviceModel(), 'mplsServices', $os->discoverMplsServices());

            echo "\nMPLS SAPs: ";
            ModuleModelObserver::observe('\App\Models\MplsSap');
            $this->syncModels($os->getDeviceModel(), 'mplsSaps', $os->discoverMplsSaps($svcs));

            echo "\nMPLS SDP Bindings: ";
            ModuleModelObserver::observe('\App\Models\MplsSdpBind');
            $this->syncModels($os->getDeviceModel(), 'mplsSdpBinds', $os->discoverMplsSdpBinds($sdps, $svcs));

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
            $device = $os->getDeviceModel();

            if ($device->mplsLsps()->exists()) {
                echo "\nMPLS LSPs: ";
                ModuleModelObserver::observe('\App\Models\MplsLsp');
                $lsps = $this->syncModels($device, 'mplsLsps', $os->pollMplsLsps());
            }

            if ($device->mplsLspPaths()->exists()) {
                echo "\nMPLS LSP Paths: ";
                ModuleModelObserver::observe('\App\Models\MplsLspPath');
                $this->syncModels($device, 'mplsLspPaths', $os->pollMplsPaths($lsps));
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
        $os->getDeviceModel()->mplsLsps()->delete();
        $os->getDeviceModel()->mplsLspPaths()->delete();
        $os->getDeviceModel()->mplsSdps()->delete();
        $os->getDeviceModel()->mplsServices()->delete();
        $os->getDeviceModel()->mplsSaps()->delete();
        $os->getDeviceModel()->mplsSdpBinds()->delete();
    }
}
