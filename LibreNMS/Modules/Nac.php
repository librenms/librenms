<?php
/**
 * Nac.php
 *
 * network access controls module
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\PortsNac;
use App\Observers\ModuleModelObserver;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\OS;

class Nac implements Module
{
    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param OS $os
     */
    public function discover(OS $os)
    {
        // not implemented
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
        if ($os instanceof NacPolling) {
            // discovery output (but don't install it twice (testing can can do this)
            ModuleModelObserver::observe(PortsNac::class);

            $nac_entries = $os->pollNac()->keyBy('mac_address');
            $existing_entries = $os->getDevice()->portsNac->keyBy('mac_address');

            // update existing models
            foreach ($nac_entries as $nac_entry) {
                if ($existing = $existing_entries->get($nac_entry->mac_address)) {
                    $nac_entries->put($nac_entry->mac_address, $existing->fill($nac_entry->attributesToArray()));
                }
            }

            // persist to DB
            $os->getDevice()->portsNac()->saveMany($nac_entries);

            $delete = $existing_entries->diffKeys($nac_entries)->pluck('ports_nac_id');
            if ($delete->isNotEmpty()) {
                $count = PortsNac::query()->whereIn('ports_nac_id', $delete)->delete();
                d_echo('Deleted ' . $count, str_repeat('-', $count));
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
        $os->getDevice()->portsNac()->delete();
    }
}
