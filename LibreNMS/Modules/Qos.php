<?php
/**
 * Qos.php
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
 * @copyright  2024 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\QosDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\QosPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;

class Qos implements Module
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
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof QosDiscovery;
    }

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param  \LibreNMS\OS  $os
     */
    public function discover(OS $os): void
    {
        echo('discover()');
        if ($os instanceof QosDiscovery) {
            echo('instanceof');
            $qos = $os->discoverQos();
            ModuleModelObserver::observe(\App\Models\Qos::class);
            $qos = $this->syncModels($os->getDevice(), 'qos', $qos);
            $os->setQosParents($qos);
        }
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof QosPolling;
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
        if ($os instanceof QosPolling) {
            // Gather our SLA's from the DB.
            $qos = $os->getDevice()->qos()
                ->where('disabled', 0)->get();

            if ($qos->isNotEmpty()) {
                // We have QoS to poll
                $os->pollQos($qos);
                $os->getDevice()->qos()->saveMany($qos);

                // Update RRD and set data rates
                foreach ($qos as $thisQos) {
                    $poll_interval = $thisQos->getOriginal('last_polled') - $thisQos->last_polled;
                    if ($poll_interval > 0) {
                        $thisQos->traffic_out_rate = $this->calcRate($thisQos->last_traffic_out, $thisQos->getOriginal('last_traffic_out'), $poll_interval);
                        $thisQos->traffic_in_rate = $this->calcRate($thisQos->last_traffic_in, $thisQos->getOriginal('last_traffic_in'), $poll_interval);
                        $thisQos->drop_out_rate = $this->calcRate($thisQos->last_drop_out, $thisQos->getOriginal('last_drop_out'), $poll_interval);
                        $thisQos->drop_in_rate = $this->calcRate($thisQos->last_drop_in, $thisQos->getOriginal('last_drop_in'), $poll_interval);
                    }

                    // TODO: per-type update
                    switch ($thisQos->type) {
                        case 'routeros_simple':
                            $rrd_name = ['routeros-simplequeue', $thisQos->rrd_id];
                            $rrd_def = RrdDefinition::make()
                                ->addDataset('sentbytesin', 'COUNTER', 0)
                                ->addDataset('sentbytesout', 'COUNTER', 0)
                                ->addDataset('dropbytesin', 'COUNTER', 0)
                                ->addDataset('dropbytesout', 'COUNTER', 0);
                            $rrd_data = [
                                'sentbytesin' => $thisQos->last_traffic_in,
                                'sentbytesout' => $thisQos->last_traffic_out,
                                'dropbytesin' => $thisQos->last_drop_in,
                                'dropbytesout' => $thisQos->last_drop_out,
                            ];
                            break;
                        case 'routeros_tree':
                            $rrd_name = ['routeros-queuetree', $thisQos->rrd_id];
                            $rrd_def = RrdDefinition::make()
                                ->addDataset('sentbytes', 'COUNTER', 0)
                                ->addDataset('dropbytes', 'COUNTER', 0);
                            $rrd_data = [
                                'sentbytes' => $thisQos->last_traffic_out,
                                'dropbytes' => $thisQos->last_drop_out,
                            ];
                            break;
                        default:
                            $rrd_name = null;
                            echo('Queue type |' . $thisQos->type . "| has not been implmeneted in LibreNMS/Modules/Qos.php\n");
                    }
                    if (! is_null($rrd_name)) {
                        $datastore->put($os->getDeviceArray(), 'qos', [ 'rrd_name' => $rrd_name, 'rrd_def' => $rrd_def ], $rrd_data);
                    }
                }
            }
        }
    }

    public function dataExists(Device $device): bool
    {
        return $device->qos()->exists();
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     */
    public function cleanup(Device $device): int
    {
        return $device->qos()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'slas' => $device->qos()->orderBy('title')
                ->get()->map->makeHidden(['device_id', 'id']),
        ];
    }
}
