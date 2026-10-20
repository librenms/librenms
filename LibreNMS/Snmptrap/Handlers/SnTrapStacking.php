<?php

/**
 * SnTrapStacking.php
 *
 * -Description-
 *
 * Foundry/Ruckus FastIron traps for stack membership, roles, mode and power.
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
use Illuminate\Support\Str;
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
        $message = $trap->getOidData($trap->findOid('FOUNDRY-SN-AGENT-MIB::snAgGblTrapMessage'));

        $trap->log($message === '' ? $this->describe($trap) : $message, $this->severity($trap->getTrapOid()));
    }

    /**
     * The trap's name and the unit and power supply it is about, for a trap
     * that carries no message.
     */
    private function describe(Trap $trap): string
    {
        $details = [];
        foreach (['stack unit' => 'snChasUnitIndex', 'power supply' => 'snChasPwrSupplyIndex'] as $label => $object) {
            $value = $trap->getOidData($trap->findOid("FOUNDRY-SN-AGENT-MIB::$object"));
            if ($value !== '') {
                $details[] = "$label $value";
            }
        }
        $name = Str::after($trap->getTrapOid(), '::');

        return $details ? "$name: " . implode(', ', $details) : $name;
    }

    private function severity(string $trapOid): Severity
    {
        return match (Str::after($trapOid, '::')) {
            'snTrapStackingUnitDeleted',
            'snTrapStackingMasterElected',
            'snTrapStackingStandByChangedStandalone',
            'snTrapStackingIgnoreShutdownTemperatureThresholdEnabled' => Severity::Warning,
            'snTrapStackingChasPwrSupplyFailed' => Severity::Error,
            'snTrapStackingChasPwrSupplyOK' => Severity::Ok,
            default => Severity::Notice,
        };
    }
}
