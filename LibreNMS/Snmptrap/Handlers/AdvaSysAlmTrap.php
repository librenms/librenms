<?php
/**
 * AdvaSysAlmTrap.php
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
 * Adva system alarm traps. This handler will log the description and a
 * description of the alarm.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class AdvaSysAlmTrap implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param  Device  $device
     * @param  Trap  $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $alSeverity = $trap->getOidData($trap->findOid('CM-ALARM-MIB::cmSysAlmNotifCode'));
        $logSeverity = match ($alSeverity) {
            'critical' => Severity::Error,
            'major' => Severity::Warning,
            'minor' => Severity::Notice,
            'cleared' => Severity::Ok,
            default => Severity::Info,
        };

        $sysAlmDescr = $trap->getOidData($trap->findOid('CM-ALARM-MIB::cmSysAlmDescr'));
        $trap->log("System Alarm: $sysAlmDescr Status: $alSeverity", $logSeverity);
    }
}
