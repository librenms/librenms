<?php
/**
 * JnxDomAlarmSet.php
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
 * @copyright  2018 KanREN, Inc.
 * @author     Neil Kahle <nkahle@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class JnxDomAlarmSet implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param Device $device
     * @param Trap $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $currentAlarm = $trap->getOidData($trap->findOid('JUNIPER-DOM-MIB::jnxDomCurrentAlarms'));
        $ifDescr = $trap->getOidData($trap->findOid('IF-MIB::ifDescr'));
        $alarmList = $this->getAlarms($currentAlarm);
        Log::event("DOM alarm set for interface $ifDescr. Current alarm\(s\): $alarmList", $device->device_id , 'trap', 2);

        #Show raw snmp trap information. Useful for debuging.
        $raw = $trap->getRaw();
        Log::event("$raw", $device->device_id , 'trap', 2);
    }

    public static function getAlarms($currentAlarm)
    {
        $alarmBin = preg_split("//",
            decbin(hexdec(str_replace(" ", "", $currentAlarm))),
            -1, PREG_SPLIT_NO_EMPTY);
        $alarmDescr = getAlarmDescr();
        $x = 0;
        foreach ($alarmBin as $syntax) {
            if ($syntax == 1) {
                $descr[$x] = $alarmDescr[$x];
            }
            $x++;
        }
        $message = implode(', ', $descr);
        return $message;
    }

    public static function getAlarmDescr()
    {
        return [
        'input Loss of signal',
        'input Loss of Lock',
        'input rx path not ready',
        'input laser power high',
        'input laser power low',
        'output laser bias current high',
        'output laser bias current low',
        'output laser power high',
        'output laser power low',
        'output data not ready',
        'output tx path not ready',
        'output laser fault',
        'output loss of lock',
        'module temperature high',
        'module temperature low',
        'module not ready',
        'module power down',
        'wire unplugged or down',
        'module unplugged or down',
        'module voltage high',
        'module voltage low',
        ];
    }
}
