<?php
/**
 * OccamEcfmNotification.php
 *
 * Handles the following traps:
 * - OCCAM-NOTIFICATION-MIB::ecfmNotification
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
 *
 * @copyright  2021 Vantage Point Solutions
 * @author     Eric Graham <eric.graham@vantagepnt.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class OccamEcfmNotification implements SnmptrapHandler
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
        $shelfIndex = $slotIndex = $ecfmMepid = $ecfmPortId = $ecfmDomainLevel = $ecfmVlanId = $ecfmRemoteMepId = $ecfmAlarmType = $ecfmAlarmStatus = "unknown";

        if ($trap_oid = $trap->findOid('OCCAM-SHELF-MIB::cardShelfIndex'))
            $shelfIndex = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-SHELF-MIB::cardSlotIndex'))
            $slotIndex = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-NOTIFICATION-MIB::ecfmMepid'))
            $ecfmMepid = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-NOTIFICATION-MIB::ecfmPortId'))
            $ecfmPortId = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-NOTIFICATION-MIB::ecfmDomainLevel'))
            $ecfmDomainLevel = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-NOTIFICATION-MIB::ecfmVlanId'))
            $ecfmVlanId = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-NOTIFICATION-MIB::ecfmRemoteMepId'))
            $ecfmRemoteMepId = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-NOTIFICATION-MIB::ecfmAlarmType'))
            $ecfmAlarmType = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-NOTIFICATION-MIB::ecfmAlarmStatus'))
            $ecfmAlarmStatus = $trap->getOidData($trap_oid);

        $alarmTypeDescs = array(
            'rdi' => 'Mep received RDI',
            'loc' => 'Mep detected loss of continuity with peer MEP',
            'unexpCCM' => 'MEP received CCM with incorrect interval',
            'unexpMep' => 'MEP received a CCM (correct MEG ID and level) but with unexpected MEP ID or unexpected receiver ID',
            'misMerge' => 'MEP received a CCM with incorrect domain ID',
            'unexpMegLevel' => 'MEP received a CCM with a wrong level',
            'ais' => 'MEP received AIS frame',
            'crossConnect' => 'MEP received a CCM with incorrect MEP or incorrect MAID',
            'ccmDefect' => 'MEP received at least one invalid CCM whose CCM interval has not yet timed out',
            'remoteCCMDefect' => 'have not received a CCM in 3.5 CCM intervals',
            'macStatusDefect' => 'Remote MEP has a port status value other than psUp'
        )

        $ecfmAlarmDesc = "alarm description unknown";
        if (array_key_exists($ecfmAlarmType, $alarmTypeDescs))
            $ecfmAlarmDesc = $alarmTypeDescs[$ecfmAlarmType];

        $logMessage = "ECFM Notification: $ecfmAlarmDesc (alarm status $ecfmAlarmStatus); shelf/slot $shelfIndex/$slotIndex; ECFM details: local MEP ID $ecfmMepid, remote MEP ID $ecfmRemoteMepId, port $ecfmPortId VLAN $ecfmVlanId, domain level $ecfmDomainLevel";
        Log::event($logMessage, $device->device_id, 'trap', 3);
    }
}
