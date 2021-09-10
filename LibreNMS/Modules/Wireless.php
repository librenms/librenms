<?php
/**
 * Wireless.php
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 * @copyright  2021 Otto Reinikainen
 * @author     Otto Reinikainen <otto@ottorei.fi>
 */

namespace LibreNMS\Modules;
use App\Models\AccessPoint;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\WirelessAccessPointPolling;
use LibreNMS\OS;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;

class Wireless implements Module
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
        if ($os instanceof WirelessAccessPointPolling) {
            echo "\nPoll Wireless Access Points: ";
            $access_points = new Collection;

            // Get APs from controller
            $access_points = $os->pollWirelessAccessPoints()->keyBy(function ($item) {
                return $item->getCompositeKey();
            });

            // Update counters
            $os->discoverWirelessApCount();
            // clients?

            // Sync models. In a situation where controller is changed, update existing APs
            // TODO: Restore soft-deleted AP if found again
            ModuleModelObserver::observe('\App\Models\AccessPoint');
            $this->syncModels($os->getDevice(), 'accessPoints', $access_points);

            // TODO: Update RRDs

/*             $rrd_name = ['arubaap',  $name . $radionum];

            $rrd_def = RrdDefinition::make()
                ->addDataset('channel', 'GAUGE', 0, 200)
                ->addDataset('txpow', 'GAUGE', 0, 200)
                ->addDataset('radioutil', 'GAUGE', 0, 100)
                ->addDataset('nummonclients', 'GAUGE', 0, 500)
                ->addDataset('nummonbssid', 'GAUGE', 0, 200)
                ->addDataset('numasoclients', 'GAUGE', 0, 500)
                ->addDataset('interference', 'GAUGE', 0, 2000);

            $fields = [
                'channel'         => $channel,
                'txpow'           => $txpow,
                'radioutil'       => $radioutil,
                'nummonclients'   => $nummonclients,
                'nummonbssid'     => $nummonbssid,
                'numasoclients'   => $numasoclients,
                'interference'    => $interference,
            ];

            $tags = [
                'name' => $name,
                'radionum' => $radionum,
                'rrd_name' => $rrd_name,
                'rrd_def' => $rrd_def,
            ];

            data_update($device, 'aruba', $tags, $fields); */

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
        $os->getDevice()->accessPoints()->delete();
    }
}
