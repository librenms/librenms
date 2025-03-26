<?php
/**
 * FgTrapAvVirus.php
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
 * The Fortigate Antivirus feature detected a virus. Nothing to do.
 *
 * @link       https://www.librenms.org
 *
 * @author     Stephan Leruth <Stephan.Leruth@ineos.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class FgTrapAvVirus implements SnmptrapHandler
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
        $VirusName = $trap->getOidData($trap->findOid('FORTINET-FORTIGATE-MIB::fgAvTrapVirName'));
        $trap->log("A virus has been detected by the anti-virus engine on $device->hostname. Virus Name: $VirusName", Severity::Warning);
    }
}
