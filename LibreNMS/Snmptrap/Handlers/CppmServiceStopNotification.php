<?php
/**
 * CppmServiceStopNotification.php
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
 * cppmServiceStopNotification trap indicates stopping of ClearPass service.
 * Contains:
 * <cppmServiceName> indicates name of the service being stopped.
 * <cppmServiceStatus> indicates status of the service stop operation.
 * <cppmTrapMessage> contains service messages.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2024 Dag Bakke
 * @author     Dag Bakke <dag@bakke.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class CppmServiceStopNotification implements SnmptrapHandler
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
        $cppmServiceName = $trap->getOidData($trap->findOid('CPPM-MIB::cppmServiceName.0'));
        $cppmTrapMessage = $trap->getOidData($trap->findOid('CPPM-MIB::cppmTrapMessage.0'));
        $trap->log('Clearpass Service Trap - Host:' . $device->displayName() . ' Service:' . $cppmServiceName . ' Message:' . $cppmTrapMessage, Severity::Warning);
    }
}
