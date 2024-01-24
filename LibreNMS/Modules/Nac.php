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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\PortsNac;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Facades\DB;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class Nac implements Module
{
    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return false;
    }

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param  Os  $os
     */
    public function discover(OS $os): void
    {
        // not implemented
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof NacPolling;
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param  \LibreNMS\OS  $os
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if ($os instanceof NacPolling) {
            // discovery output (but don't install it twice (testing can can do this)
            ModuleModelObserver::observe(PortsNac::class);

            $nac_entries = $os->pollNac()->keyBy('mac_address');
            //filter out historical entries
            $existing_entries = $os->getDevice()->portsNac->keyBy('mac_address')->filter(function ($value, $key) {
                if ($value['historical'] == 0) {
                    return $value;
                }
            });

            // update existing models
            foreach ($nac_entries as $nac_entry) {
                if ($existing = $existing_entries->get($nac_entry->mac_address)) {
                    // we have the same mac_address once again. Let's decide if we should keep the existing as history or not.
                    if (($nac_entry->port_id == $existing->port_id) ||
                        ($nac_entry->method == $existing->method) ||
                        ($nac_entry->vlan == $existing->vlan) ||
                        ($nac_entry->authz_by == $existing->authz_by) ||
                        ($nac_entry->authz_status == $existing->authz_status) ||
                        ($nac_entry->ip_address == $existing->ip_address) ||
                        ($nac_entry->username == $existing->username)) {
                        // if everything is similar, we update current entry. If not, we duplicate+history
                        $nac_entries->put($nac_entry->mac_address, $existing->fill($nac_entry->attributesToArray()));
                    }
                }
            }

            // persist to DB
            $os->getDevice()->portsNac()->saveMany($nac_entries);

            $age = $existing_entries->diffKeys($nac_entries)->pluck('ports_nac_id');
            if ($age->isNotEmpty()) {
                $count = PortsNac::query()->whereIntegerInRaw('ports_nac_id', $age)->update(['historical' => true, 'updated_at' => DB::raw('updated_at')]);
                d_echo('Aged ' . $count, str_repeat('-', $count));
            }
        }
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     */
    public function cleanup(Device $device): void
    {
        $device->portsNac()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'ports_nac' => $device->portsNac()->orderBy('ports.ifIndex')->orderBy('mac_address')
                ->leftJoin('ports', 'ports_nac.port_id', 'ports.port_id')
                ->select(['ports_nac.*', 'ifIndex'])
                ->get()->map->makeHidden(['ports_nac_id', 'device_id', 'port_id', 'updated_at', 'created_at']),
        ];
    }
}
