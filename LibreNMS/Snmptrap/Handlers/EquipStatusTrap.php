<?php
/**
 * equipStatusTrap.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Vitali Kari
 * @author     Vitali Kari <vitali.kari@gmail.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class EquipStatusTrap implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param  Device  $device
     * @param  Trap  $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap): void
    {
        $state = $trap->getOidData('EQUIPMENT-MIB::equipStatus.0');

        $severity = $this->getSeverity($state);
        $trap->log('SNMP Trap: Equipment Status  ' . $state, $severity, 'state');
    }

    private function getSeverity(string $state): Severity
    {
        return match ($state) {
            'warning', '3', 'major', '5' => Severity::Warning,
            'critical', '4' => Severity::Error,
            'minor', '2' => Severity::Notice,
            'nonAlarmed', '1' => Severity::Ok,
            default => Severity::Unknown,
        };
    }
}
