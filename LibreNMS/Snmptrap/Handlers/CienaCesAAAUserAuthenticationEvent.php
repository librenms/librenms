<?php
/**
 * CienaCesAAAUserAuthenticationEvent.php
 *
 * -Description-
 *
 * Handles Ciena authentication traps. States whether or not user
 * successfully authenticates to the device.
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
 * @copyright  2024 KanREN Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class CienaCesAAAUserAuthenticationEvent implements SnmptrapHandler
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
        $user = $trap->getOidData($trap->findOid('CIENA-CES-AAA-MIB::cienaCesAAAUserName'));
        $message = $trap->getOidData($trap->findOid('CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationDescription'));
        $severity = Severity::Notice;
        if ($trap->getOidData($trap->findOid('CIENA-CES-AAA-MIB::cienaCesAAAUserAuthenticationStatus')) == 'failure') {
            $severity = Severity::Warning;
        }
        $trap->log("Authentication attempt by $user. $message", $severity);
    }
}
