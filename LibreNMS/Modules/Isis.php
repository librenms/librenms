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
        // Get device object
        $device = $os->getDevice();
        $device_id = $os->getDeviceId();

        // Poll all ISIS enabled circuits from the device
        $circuits_poll = $os->getCacheTable('ISIS-MIB::isisCirc', 'ISIS-MIB');

        // Poll all available adjacencies
        $adjacencies_poll = $os->getCacheTable('ISIS-MIB::isisISAdj', 'ISIS-MIB');
        $adjacencies = collect();
        $isis_data = [];

        // Translate system state codes into meaningful strings
        $isis_codes['1'] = 'L1';
        $isis_codes['2'] = 'L2';
        $isis_codes['3'] = 'L1L2';
        $isis_codes['4'] = 'unknown';

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
                    //var_dump($adjacencies_poll[$circuit]['isisISAdjState']);
                    // Adjancy is UP
                    if (end($adjacencies_poll[$circuit]['isisISAdjState']) == '3') {
                        $isis_data['isisISAdjState'] = end($adjacencies_poll[$circuit]['isisISAdjState']);
                        $isis_data['isisISAdjNeighSysID'] = end($adjacencies_poll[$circuit]['isisISAdjNeighSysID']);
                        $isis_data['isisISAdjNeighSysType'] = end($adjacencies_poll[$circuit]['isisISAdjNeighSysType']);
                        $isis_data['isisISAdjNeighPriority'] = end($adjacencies_poll[$circuit]['isisISAdjNeighPriority']);
                        $isis_data['isisISAdjLastUpTime'] = end($adjacencies_poll[$circuit]['isisISAdjLastUpTime']);
                        $isis_data['isisISAdjAreaAddress'] = end(end($adjacencies_poll[$circuit]['isisISAdjAreaAddress']));
                        $isis_data['isisISAdjIPAddrType'] = end(end($adjacencies_poll[$circuit]['isisISAdjIPAddrType']));
                        $isis_data['isisISAdjIPAddrAddress'] = end(end($adjacencies_poll[$circuit]['isisISAdjIPAddrAddress']));

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
                        * Adjancency is configured on the device but not available
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
            echo "\nFound " . $adjacencies->count() . " configured adjacencies";
        }

        // Cleanup -> needs testing
        IsisAdjacency::query()
            ->where(['device_id' => $device['device_id']])
            ->whereNotIn('ifIndex', $adjacencies->pluck('ifIndex'))->delete();
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
