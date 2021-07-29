<?php
/**
 * Isis.php
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
 * @link       http://librenms.org
 * @copyright  2021 Otto Reinikainen
 * @author     Otto Reinikainen <otto@ottorei.fi>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\IsisAdjacency;
use Illuminate\Support\Arr;
use LibreNMS\Component;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\OS\Junos;
use LibreNMS\Util\IP;

class Isis implements Module
{
    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param OS $os
     */
    public function discover(OS $os)
    {
        $device_array = $os->getDeviceArray();
        $device_id = $os->getDeviceId();
        $options = [
            'filter' => [
                'device_id' => ['=', $device_id],
                'type' => ['=', 'ISIS'],
            ],
        ];

        $component = new Component();
        $components = $component->getComponents($device_id, $options);

        // Check if the device has any ISIS enabled interfaces
        $circuits_poll = snmpwalk_group($device_array, 'ISIS-MIB::isisCirc', 'ISIS-MIB');

        // No ISIS enabled interfaces -> delete the component
        if (empty($circuits_poll)) {
            if (isset($components[$device_id])) {
                foreach ($components[$device_id] as $component_id => $_unused) {
                    $component->deleteComponent($component_id);
                }
                echo "\nISIS components deleted";
            }

            // ISIS enabled interfaces found -> create the component
        } else {
            if (isset($components[$device_id])) {
                $isis_component = $components[$device_id];
            } else {
                $isis_component = $component->createComponent($device_id, 'ISIS');
            }

            $component->setComponentPrefs($device_id, $isis_component);
            echo "\nISIS component updated";
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
        // Translate system state codes into meaningful strings
        $isis_codes = ['1' => 'L1',
            '2' => 'L2',
            '3' => 'L1L2',
            '4' => 'unknown',
        ];

        // Get device objects
        $device_array = $os->getDeviceArray();
        $device = $os->getDevice();
        $device_id = $os->getDeviceId();

        // Check if device has any ISIS enabled circuits previously discovered
        $options = [
            'filter' => [
                'device_id' => ['=', $device_id],
                'type' => ['=', 'ISIS'],
            ],
        ];

        $component = new Component();
        $components = $component->getComponents($device_id, $options);

        if (! empty($components)) {

            // Poll all ISIS enabled interfaces from the device
            $circuits_poll = snmpwalk_group($device_array, 'ISIS-MIB::isisCirc', 'ISIS-MIB');

            // Poll all available adjacencies
            $adjacencies_poll = snmpwalk_group($device_array, 'ISIS-MIB::isisISAdj', 'ISIS-MIB');
            $adjacencies = collect();
            $isis_data = [];

            if ($os instanceof Junos) {
                // Do not poll loopback interface
                unset($circuits_poll['16']);
            }

            // Loop through all configured adjacencies on the device
            foreach ($circuits_poll as $circuit => $circuit_data) {
                if (is_numeric($circuit)) {
                    echo "\nAdjacency found on ifIndex: " . $circuit;
                    $port_id = (int) $device->ports()->where('ifIndex', $circuit)->value('port_id');

                    if ($circuit_data['isisCircPassiveCircuit'] != '1') {
                        // Adjacency is UP
                        if (! empty($adjacencies_poll[$circuit]) && Arr::last($adjacencies_poll[$circuit]['isisISAdjState']) == '3') {
                            $isis_data['isisISAdjState'] = Arr::last($adjacencies_poll[$circuit]['isisISAdjState']);
                            $isis_data['isisISAdjNeighSysID'] = Arr::last($adjacencies_poll[$circuit]['isisISAdjNeighSysID']);
                            $isis_data['isisISAdjNeighSysType'] = Arr::last($adjacencies_poll[$circuit]['isisISAdjNeighSysType']);
                            $isis_data['isisISAdjNeighPriority'] = Arr::last($adjacencies_poll[$circuit]['isisISAdjNeighPriority']);
                            $isis_data['isisISAdjLastUpTime'] = Arr::last($adjacencies_poll[$circuit]['isisISAdjLastUpTime']);
                            $isis_data['isisISAdjAreaAddress'] = Arr::last(Arr::last($adjacencies_poll[$circuit]['isisISAdjAreaAddress']));
                            $isis_data['isisISAdjIPAddrType'] = Arr::last(Arr::last($adjacencies_poll[$circuit]['isisISAdjIPAddrType']));
                            $isis_data['isisISAdjIPAddrAddress'] = Arr::last(Arr::last($adjacencies_poll[$circuit]['isisISAdjIPAddrAddress']));

                            // Format data
                            $isis_data['isisISAdjNeighSysID'] = str_replace(' ', '.', $isis_data['isisISAdjNeighSysID']);
                            $isis_data['isisISAdjLastUpTime'] = (int) $isis_data['isisISAdjLastUpTime'] / 100;
                            $isis_data['isisISAdjAreaAddress'] = str_replace(' ', '.', $isis_data['isisISAdjAreaAddress']);

                            // Save data into the DB
                            $adjacency = IsisAdjacency::updateOrCreate([
                                'device_id' => $device_id,
                                'ifIndex' => $circuit,
                            ], [
                                'device_id' => $device_id,
                                'ifIndex' => $circuit,
                                'port_id' => $port_id,
                                'isisISAdjState' => 'up',
                                'isisISAdjNeighSysType' => $isis_codes[$isis_data['isisISAdjNeighSysType']],
                                'isisISAdjNeighSysID' => $isis_data['isisISAdjNeighSysID'],
                                'isisISAdjNeighPriority' => $isis_data['isisISAdjNeighPriority'],
                                'isisISAdjLastUpTime' => $isis_data['isisISAdjLastUpTime'],
                                'isisISAdjAreaAddress' => $isis_data['isisISAdjAreaAddress'],
                                'isisISAdjIPAddrType' => $isis_data['isisISAdjIPAddrType'],
                                'isisISAdjIPAddrAddress' => IP::fromHexstring($isis_data['isisISAdjIPAddrAddress']),
                            ]);
                        } else {
                            /*
                            * Adjacency is configured on the device but not available
                            * Update existing record to down state
                            * Set the status of the adjacency to down
                            * Also if the adjacency was never up, create a record
                            */
                            if ($circuit_data['isisCircAdminState'] != '1') {
                                $state = 'disabled';
                            } else {
                                $state = 'down';
                            }
                            $adjacency = IsisAdjacency::updateOrCreate([
                                'device_id' => $device_id,
                                'ifIndex' => $circuit,
                            ], [
                                'device_id' => $device_id,
                                'ifIndex' => $circuit,
                                'port_id' => $port_id,
                                'isisISAdjState' => $state,
                            ]);
                        }
                        $adjacencies->push($adjacency);
                    }
                }
            }

            echo "\nFound " . $adjacencies->count() . ' configured adjacencies';

            // Cleanup
            IsisAdjacency::query()
                ->where(['device_id' => $device['device_id']])
                ->whereNotIn('ifIndex', $adjacencies->pluck('ifIndex'))->delete();
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
        $os->getDevice()->isisAdjacencies()->delete();
    }
}
