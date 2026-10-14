<?php

/**
 * SnTrapStacking.php
 *
 * -Description-
 *
 * Foundry/Ruckus FastIron traps for stack membership, roles and power.
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
 * @copyright  2026 LibreNMS contributors
 * @author     Andrei-Alexandru Bleortu
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class SnTrapStacking implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * The switch names the unit and what happened to it in snAgGblTrapMessage,
     * e.g. "Stack: Stack unit 6 has been deleted from the stack system".
     *
     * @param  Device  $device
     * @param  Trap  $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $message = trim((string) $trap->getOidData($trap->findOid('FOUNDRY-SN-AGENT-MIB::snAgGblTrapMessage')));
        if ($message === '') {
            $unit = $trap->getOidData($trap->findOid('FOUNDRY-SN-AGENT-MIB::snChasUnitIndex'));
            $message = $trap->getTrapOid() . " for stack unit $unit";
        }

        $trap->log($message, $this->severity($trap->getTrapOid()));
    }

    private function severity(string $trapOid): Severity
    {
        return match ($trapOid) {
            'FOUNDRY-SN-NOTIFICATION-MIB::snTrapStackingUnitDeleted',
            'FOUNDRY-SN-NOTIFICATION-MIB::snTrapStackingMasterElected' => Severity::Warning,
            'FOUNDRY-SN-NOTIFICATION-MIB::snTrapStackingChasPwrSupplyFailed' => Severity::Error,
            'FOUNDRY-SN-NOTIFICATION-MIB::snTrapStackingChasPwrSupplyOK' => Severity::Ok,
            default => Severity::Notice,
        };
    }
}
