<?php
/*
 * Coriant.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\TnmsneInfo;
use App\Observers\ModuleModelObserver;
use LibreNMS\Interfaces\Polling\OSPolling;
use Log;

class Coriant extends \LibreNMS\OS implements OSPolling
{
    public function pollOS()
    {
        echo 'TNMS-NBI-MIB: enmsNETable';

        /*
         * Coriant have done some SQL over SNMP, since we have to populate and update all the tables
         * before using it, we have to do ugly stuff
         */

        $c_list = [];
        ModuleModelObserver::observe('\App\Models\MplsLsp\TnmsneInfo');

        foreach (snmpwalk_cache_multi_oid($this->getDeviceArray(), 'enmsNETable', [], 'TNMS-NBI-MIB') as $index => $ne) {
            $ne = TnmsneInfo::firstOrNew(['device_id' => $this->getDeviceId(), 'neID' => $index], [
                'device_id' => $this->getDeviceId(),
                'neID' => $index,
                'neType' => $ne['enmsNeType'],
                'neName' => $ne['enmsNeName'],
                'neLocation' => $ne['enmsNeLocation'],
                'neAlarm' => $ne['enmsNeAlarmSeverity'],
                'neOpMode' => $ne['enmsNeOperatingMode'],
                'neOpState' => $ne['enmsNeOpState'],
            ]);

            if ($ne->isDirty()) {
                $ne->save();
                Log::event("Coriant enmsNETable Hardware $ne->neType : $ne->neName ($index) at $ne->neLocation Discovered", $this->getDevice(), 'system', 2);
            }
            $c_list[] = $index;
        }

        foreach (TnmsneInfo::where('device_id', $this->getDeviceId())->whereNotIn('neID', $c_list)->get() as $ne) {
            /** @var TnmsneInfo $ne */
            $ne->delete();
            Log::event("Coriant enmsNETable Hardware $ne->neName at $ne->neLocation Removed", $this->getDevice(), 'system', $ne->neID);
        }
    }
}
