<?php
/**
 * MgmtTrapNmsEventTest.php
 *
 * -Description-
 *
 * Tests NMS Event Traps sent by Ekinops Optical Networking products.
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
 *
 * Tests JnxVpnPwDown and JnxVpnPwUp traps from Juniper devices.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2020 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

class MgmtTrapNmsEventTest extends SnmpTrapTestCase
{
    public function testEvent(): void
    {
        $alarm = self::genEkiEvent();

        $this->assertTrapLogsMessage("{{ hostname }}
UDP: [{{ ip }}]:60057->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 159:19:33:14.42
SNMPv2-MIB::snmpTrapOID.0 EKINOPS-MGNT2-NMS-MIB::mgnt2TrapNMSEvent
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogNotificationId 132
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogObjectClassIdentifier chassis
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourcePm {$alarm['srcPm']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogBoardNumber {$alarm['slotNum']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourcePortType None
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourcePortNumber 0
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogEventType activityLog
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourceType event
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogReason {$alarm['reason']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogAdditionalText 
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogTime 2020-8-10,14:22:5
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogNodeControllerIpAddress 0.0.0.0
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogChassisId {{ ip }}",
            "Event on slot {$alarm['slotNum']}, {$alarm['srcPm']} Reason: {$alarm['reason']}",
            'Could not handle mgnt2TrapNMSEvent trap'
        );
    }

    //Test alarm with addtional text supplied.
    public function testEventAddText(): void
    {
        $alarm = self::genEkiEvent();

        $this->assertTrapLogsMessage("{{ hostname }}
UDP: [{{ ip }}]:60057->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 159:19:33:14.42
SNMPv2-MIB::snmpTrapOID.0 EKINOPS-MGNT2-NMS-MIB::mgnt2TrapNMSEvent
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogNotificationId 132
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogObjectClassIdentifier chassis
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourcePm {$alarm['srcPm']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogBoardNumber {$alarm['slotNum']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourcePortType None
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourcePortNumber 0
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogEventType activityLog
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourceType event
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogReason {$alarm['reason']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogAdditionalText {$alarm['addText']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogTime 2020-8-10,14:22:5
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogNodeControllerIpAddress 0.0.0.0
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogChassisId {{ ip }}",
            "Event on slot {$alarm['slotNum']}, {$alarm['srcPm']} Reason: {$alarm['reason']} Additional info: {$alarm['addText']}",
            'Could not handle mgnt2TrapNMSEvent trap with additional text'
        );
    }

    //Event trap on a specific port
    public function testEventPort(): void
    {
        $alarm = self::genEkiEvent();

        $this->assertTrapLogsMessage("{{ hostname }}
UDP: [{{ ip }}]:60057->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 159:19:33:14.42
SNMPv2-MIB::snmpTrapOID.0 EKINOPS-MGNT2-NMS-MIB::mgnt2TrapNMSEvent
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogNotificationId 132
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogObjectClassIdentifier port
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourcePm {$alarm['srcPm']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogBoardNumber {$alarm['slotNum']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourcePortType {$alarm['portType']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourcePortNumber {alarm['portNum']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogEventType activityLog
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogSourceType event
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogReason {$alarm['reason']}
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogAdditionalText 
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogTime 2020-8-10,14:22:5
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogNodeControllerIpAddress 0.0.0.0
EKINOPS-MGNT2-NMS-MIB::mgnt2EventLogChassisId {{ ip }}",
            "Event on slot {$alarm['slotNum']}, {$alarm['srcPm']} Port: {$alarm['portType']} {alarm['portNum']}. Reason: {$alarm['reason']}",
            'Could not handle mgnt2TrapNMSEvent trap with a specified port',
        );
    }

    public static function genEkiEvent(): array
    {
        return [
            'slotNum' => rand(1, 32),
            'srcPm' => str_shuffle('0123456789abcdefg'),
            'reason' => str_shuffle('0123456789abcdefg'),
            'portType' => str_shuffle('0123456789abcdefg'),
            'portNum' => rand(1, 32),
            'addText' => str_shuffle('0123456789abcdefg'),
        ];
    }
}
