<?php
/**
 * SLA.php
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

namespace LibreNMS\Modules;

use App\Models\Sla;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Discovery\SlaDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\SlaPolling;
use LibreNMS\OS;

class Slas implements Module
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
        if ($os instanceof SlaDiscovery) {
            $slas = $os->discoverSlas();
            ModuleModelObserver::observe(Sla::class);
            $this->syncModels($os->getDevice(), 'slas', $slas);
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
        if ($os instanceof SlaPolling) {
            // Gather our SLA's from the DB.
            $slas = $os->getDevice()->slas()
                ->where('deleted', 0)->get();

            if ($slas->isNotEmpty()) {
                // We have SLA's, lets go!!!
                $os->pollSlas($slas);
                $os->getDevice()->slas()->saveMany($slas);
            }
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
        $os->getDevice()->slas()->delete();
    }
}
