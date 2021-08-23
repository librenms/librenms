<?php
/**
 * ColdBoot.php
 *
 * Handles the SNMPv2-MIB::coldStart trap
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class VeeamBackupJobCompleted implements SnmptrapHandler
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
	$name = $trap->getOidData('VEEAM-MIB::backupJobName');
	$comment = $trap->getOidData('VEEAM-MIB::backupJobComment');
	$comment .= $trap->getOidData('VEEAM-MIB::vmName');

	if ($trap->getOidData('VEEAM-MIB::backupJobResult') == 'Success'){
           Log::event('SNMP Trap: Backup success - ' . $name . '' . $comment, $device->device_id, 'backup', 1);
	}else{
	   Log::event('SNMP Trap: Backup failed - ' . $name . ' ' . $comment, $device->device_id, 'backup', 5);
	}
    }
}
