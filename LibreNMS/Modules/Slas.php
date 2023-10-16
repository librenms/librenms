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

use App\Models\Device;
use App\Models\Sla;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\SlaDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\SlaPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;

class Slas implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof SlaDiscovery;
    }

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param  \LibreNMS\OS  $os
     */
    public function discover(OS $os): void
    {
        if ($os instanceof SlaDiscovery) {
            $slas = $os->discoverSlas();
            ModuleModelObserver::observe(Sla::class);
            $this->syncModels($os->getDevice(), 'slas', $slas);
        }
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof SlaPolling;
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
        if ($os instanceof SlaPolling) {
            // Gather our SLA's from the DB.
            $slas = $os->getDevice()->slas()
                ->where('deleted', 0)->get();

            if ($slas->isNotEmpty()) {
                // We have SLA's, lets go!!!
                $os->pollSlas($slas);
                $os->getDevice()->slas()->saveMany($slas);

                // The base RRD
                foreach ($slas as $sla) {
                    $datastore->put($os->getDeviceArray(), 'sla', [
                        'sla_nr' => $sla->sla_nr,
                        'rrd_name' => ['sla', $sla->sla_nr],
                        'rrd_def' => RrdDefinition::make()->addDataset('rtt', 'GAUGE', 0, 300000),
                    ], [
                        'rtt' => $sla->rtt,
                    ]);
                }
            }
        }
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     */
    public function cleanup(Device $device): void
    {
        $device->slas()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'slas' => $device->slas()->orderBy('sla_nr')
                ->get()->map->makeHidden(['device_id', 'sla_id']),
        ];
    }
}
