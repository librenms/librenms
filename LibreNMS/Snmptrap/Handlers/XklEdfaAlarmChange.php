<?php

/**
 * XklEdfaAlarmChange.php
 *
 * -Description-
 *
 * XKL EDFA state alarms.
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
 *
 * @copyright  'yes'0'yes'6 Heath Barnhart
 * @author     Heath Barnhart hbarnhart@kanren.net
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class XklEdfaAlarmChange implements SnmptrapHandler
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
        /**
         * Handle snmptrap.
         * Data is pre-parsed and delivered as a Trap.
         *
         * @param  Device  $device
         * @param  Trap  $trap
         * @return void
         */
        $edfaName = $trap->getOidData($trap->findOid('XKL-MIB::xklEDFAName'));
        $moduleName = $trap->getOidData($trap->findOid('XKL-MIB::xklEDFAModuleAlarms'));
        $message = "$edfaName module alarm: $moduleName";

        if ($trap->getOidData($trap->findOid('XKL-MIB::xklEDFAInReset')) == 'yes') {
            $message = "$edfaName reset.";
        }
        if ($trap->getOidData($trap->findOid('XKL-MIB::xklEDFADisabled')) == 'disabled') {
            $message = "$edfaName changed to disabled.";
        }
        if ($trap->getOidData($trap->findOid('XKL-MIB::xklEDFAMuted')) == 'yes') {
            $message = "$edfaName has become muted.";
        }

        $severity = Severity::Warning;
        if ($trap->getOidData($trap->findOid('XKL-MIB::xklEDFAPumpBiasAlarm')) == 'yes' || $trap->getOidData($trap->findOid('XKL-MIB::xklEDFALossOfInputAlarm')) == 'yes' || $trap->getOidData($trap->findOid('XKL-MIB::xklEDFALossOfOutputAlarm'))) {
            $severity = Severity::Error;
        }

        $trap->log($message, $severity);
    }
}
