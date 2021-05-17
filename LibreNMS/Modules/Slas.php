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
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Slas implements Module
{
    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param OS $os
     */
    public function discover(OS $os)
    {
        // Get existing SLAs
        $existing_slas = Sla::select('sla_id')
        ->where('device_id', $os->getDevice()['device_id'])
        ->where('deleted', 0)
        ->get();

        $slas = $os->discoverSlas();

        // To ensure unity of mock sla_nr field
        $max_sla_nr = Sla::where('device_id', $os->getDevice()['device_id'])
            ->max('sla_nr');
        $i = 1;

        foreach ($slas as $sla) {
            $sla_data = Sla::select('sla_id')
                ->where('device_id', $os->getDevice()['device_id'])
                ->where('sla_nr', $sla['sla_nr'])
                ->get();
            $sla_id = $sla_data[0]->sla_id;

            if (! $sla_id) {
                // If not Cisco, set mock sla_nr to ensure unicity
                if (($os->getDevice()['os'] != 'ios') && ($os->getDevice()['os'] != 'iosxe') && ($os->getDevice()['os'] != 'iosxr')){
                    $sla['sla_nr'] = $max_sla_nr + $i;
                    $i++;
                }

                Sla::insert($sla);
                echo '+';
            } else {
                // Remove from the list
                $existing_slas = $existing_slas->except([$sla_id]);

                Sla::where('sla_id', $sla_id)
                    ->update($sla);
                echo '.';
            }
        }//end foreach

        // Mark all remaining SLAs as deleted
        foreach ($existing_slas as $existing_sla) {
            Sla::where('sla_id', $existing_sla->sla_id)
                ->update(['deleted' => 1]);
            echo '-';
        }

        echo "\n";
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
        // Gather our SLA's from the DB.
        $slas = Sla::where('device_id', $os->getDevice()['device_id'])
            ->where('deleted', 0)
            ->get();

        if (count($slas) > 0) {
            // We have SLA's, lets go!!!
            $data = $os->pollSlas($slas);
            $fields = $data[0];
            $update = $data[1];

            foreach ($slas as $sla) {
                // The base RRD
                $rrd_name = ['sla', $sla['sla_nr']];
                $rrd_def = RrdDefinition::make()->addDataset('rtt', 'GAUGE', 0, 300000);
                $tags = compact('sla_nr', 'rrd_name', 'rrd_def');
                data_update($os->getDeviceArray(), 'sla', $tags, [$fields['rtt']]);
            }

            d_echo('The following datasources were collected for #' . $sla['sla_nr'] . ":\n");
            d_echo($fields);

            // Update the DB if necessary
            if (count($update) > 0) {
                Sla::where('sla_id', $sla_id)
                ->update($update);
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
