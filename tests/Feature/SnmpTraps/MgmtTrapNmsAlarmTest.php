<?php
/**
 * MgmtTrapNmsAlarmTest.php
 *
 * -Description-
 *
 * Tests NMS Alarm Traps sent by Ekinops Optical Networking products.
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

class MgmtTrapNmsAlarmTest extends SnmpTrapTestCase
{
    public function testAlarmClear()
    {
        $alarm = self::genEkiAlarm();
        $slotNum = $alarm['slotNum'];
        $srcPm = $alarm['srcPm'];
        $specific = $alarm['specific'];

        $this->assertTrapLogsMessage("{{ hostname }}
UDP: [{{ ip }}]:60057->[192.168.1.100]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 168:19:32:11.62
SNMPv2-MIB::snmpTrapOID.0 EKINOPS-MGNT2-NMS-MIB::mgnt2TrapNMSAlarm
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogNotificationId 566098
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogObjectClassIdentifier module
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePm $srcPm
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogBoardNumber $slotNum
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePortType Other
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePortNumber 0
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogProbableCause other
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSeverity cleared
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSpecificProblem $specific
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogAdditionalText 
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogAlarmType synthesisAlarm
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogTime 2020-8-19,14:21:2.0
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogNodeControllerIpAddress 0.0.0.0
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogChassisId {{ ip }}",
            "Alarm on slot $slotNum, $srcPm Issue: $specific Possible Cause: Unknown",
            'Could not handle mgnt2TrapNMSAlarm trap CLEARED',
            [1],
    );
    }

    //Test alarm with addtional text supplied.
    public function testAlarmAddText()
    {
        $alarm = self::genEkiAlarm();
        $slotNum = $alarm['slotNum'];
        $srcPm = $alarm['srcPm'];
        $specific = $alarm['specific'];
        $add = $alarm['addText'];

        $this->assertTrapLogsMessage("{{ hostname }}
UDP: [{{ ip }}]:60057->[192.168.1.100]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 168:19:32:11.62
SNMPv2-MIB::snmpTrapOID.0 EKINOPS-MGNT2-NMS-MIB::mgnt2TrapNMSAlarm
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogNotificationId 566098
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogObjectClassIdentifier module
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePm $srcPm
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogBoardNumber $slotNum
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePortType Other
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePortNumber 0
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogProbableCause other
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSeverity cleared
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSpecificProblem $specific
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogAdditionalText $add
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogAlarmType synthesisAlarm
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogTime 2020-8-19,14:21:2.0
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogNodeControllerIpAddress 0.0.0.0
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogChassisId {{ ip }}",
            "Alarm on slot $slotNum, $srcPm Issue: $specific Additional info: $add Possible Cause: Unknown",
            'Could not handle mgnt2TrapNMSAlarm trap with additional text',
            [1],
        );
    }

    //Alarm is on a specific port
    public function testAlarmPort()
    {
        $alarm = self::genEkiAlarm();
        $slotNum = $alarm['slotNum'];
        $srcPm = $alarm['srcPm'];
        $specific = $alarm['specific'];
        $probCause = $alarm['probCause'];
        $portType = $alarm['portType'];
        $portNum = $alarm['portNum'];
        $add = $alarm['addText'];

        $this->assertTrapLogsMessage("{{ hostname }}
UDP: [{{ ip }}]:60057->[192.168.1.100]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 168:19:32:03.51
SNMPv2-MIB::snmpTrapOID.0 EKINOPS-MGNT2-NMS-MIB::mgnt2TrapNMSAlarm
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogNotificationId 566097
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogObjectClassIdentifier port
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePm $srcPm
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogBoardNumber $slotNum
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePortType $portType
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePortNumber $portNum
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogProbableCause $probCause
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSeverity critical
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSpecificProblem $specific
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogAdditionalText 
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogAlarmType integrityViolation
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogTime 2020-8-19,14:20:54.0
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogNodeControllerIpAddress 0.0.0.0
EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogChassisId {{ ip }}",
            "Alarm on slot $slotNum, $srcPm Port: $portType $portNum Issue: $specific Possible Cause: $probCause",
            'Could not handle mgnt2TrapNMSAlarm trap with additional text',
            [5],
        );
    }

    public static function genEkiAlarm()
    {
        $alarm['slotNum'] = rand(1, 32);
        $alarm['srcPm'] = str_shuffle('0123456789abcdefg');
        $alarm['specific'] = str_shuffle('0123456789abcdefg');
        $alarm['portType'] = str_shuffle('0123456789abcdefg');
        $alarm['probCause'] = str_shuffle('0123456789abcdefg');
        $alarm['portNum'] = rand(1, 32);
        $alarm['addText'] = str_shuffle('0123456789abcdefg');

        return $alarm;
    }
}
