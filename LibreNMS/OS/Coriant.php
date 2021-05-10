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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\TnmsAlarm;
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

        /* TNMS Alarm integration */
        $device = $this->getDeviceArray();
        $c_oids = snmpwalk_cache_multi_oid($device, 'enmsNETable', [], 'TNMS-NBI-MIB');
        $existing_ne = TnmsNeInfo::where('device_id', $device['device_id'])->get();
        $remove_ne = $existing_ne->keyBy('tnmsne_info_id'); // put existing ne here and remove them when we update them
        $existing_ne = $existing_ne->keyBy('neID');
        $ne_alarm_oids = snmpwalk_cache_multi_oid($device, 'enmsAlarmtable', [], 'TNMS-NBI-MIB', null, '-OQUsb');
        $existing_alarms = \App\Models\TnmsAlarm::where('device_id', $device['device_id'])->get();
        $remove_alarms = $existing_alarms->keyBy('id');
        $existing_alarms = $existing_alarms->keyBy('alarm_num');

        echo 'NE Alarms: ';
        echo PHP_EOL;

        foreach ($ne_alarm_oids as $alarm) {
            if (!empty($alarm['enmsAlAlarmNumber'])) {
                $fields = [
                    'tnmsne_info_id' => $existing_ne->get($alarm['enmsAlNEId'])->tnmsne_info_id,
                    'device_id' => $device['device_id'],
                    'alarm_num' => $alarm['enmsAlAlarmNumber'],
                    'alarm_cause' => $alarm['enmsAlProbableCauseString'],
                    'alarm_location' => $alarm['enmsAlAffectedLocation'],
                    'neAlarmtimestamp' => $alarm['enmsAlTimeStamp'],
                ];
                d_echo($fields);
                if ($fields['tnmsne_info_id']) {
                    if ($existing_alarm = $existing_alarms->get($alarm['enmsAlAlarmNumber'])) {
                        $existing_alarm->fill($fields)->save();
                        $remove_alarms->forget($existing_alarm->id);
                    } else {
                        $tnms_alarm = new TnmsAlarm($fields);
                        $tnms_alarm->save();
                    }
                }
            }
        }
       // delete old alarms
        $remove_alarms->each->delete();
    }
}
