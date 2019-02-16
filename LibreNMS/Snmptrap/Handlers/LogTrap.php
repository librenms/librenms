<?php
/**
 * logTrap.php
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
 * @copyright  2018 Vitali Kari
 * @author     Vitali Kari <vitali.kari@gmail.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class LogTrap implements SnmptrapHandler
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
        $severity = 0;
        $index = $trap->findOid('LOG-MIB::logIndex');
        $index = $trap->getOidData($index);

        $logObject = $trap->getOidData('LOG-MIB::logObject.'.$index);
        $logName = $trap->getOidData('LOG-MIB::logName.'.$index);
        $logEvent = $trap->getOidData('LOG-MIB::logEvent.'.$index);
        $logPC = $trap->getOidData('LOG-MIB::logPC.'.$index);
        $logAI = $trap->getOidData('LOG-MIB::logAI.'.$index);
        $state = $trap->getOidData('LOG-MIB::logEquipStatusV2.'.$index);

        if ($state == 'warning' or $state == 'major' or $state == '5' or $state == '3') {
            $severity = 4;
        } elseif ($state == 'critical' or $state == '4') {
            $severity = 5;
        } elseif ($state == 'minor' or $state == '2') {
            $severity = 3;
        } elseif ($state == 'nonAlarmed' or $state == '1') {
            $severity = 1;
        } else {
            $severity = 0;
        }
        log_event('SNMP Trap: Log '.$logName.' '.$logEvent.' '.$logPC.' '.$logAI.' '.$state, $device->toArray(), 'log', $severity, $device->hostname);
    }
}
