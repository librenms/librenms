<?php

/**
 * AviatIetfAlarmTest.php
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
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

final class AviatIetfAlarmTest extends SnmpTrapTestCase
{
    public function testCriticalAlarm(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmNotification
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmResource.0 "System/Root1/RFM1/Carrier1"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeId.0 "demodulator-not-locked"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeQualifier.0 "default"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmLastStateChange.0 2026-08-05,15:38:51.0,-4:0
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmPerceivedSeverity.0 critical
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmText.0 "Demodulator not locked"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmEventType.0 communicationsAlarm
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCause.0 receiverFailure
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCauseString.0 "receiverFailure"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTrendIndication.0 moreSevere
TRAP,
            'Aviat alarm on System/Root1/RFM1/Carrier1: Demodulator not locked (severity: critical) [cause: receiverFailure]',
            'Could not handle aviatIetfAlarmNotification critical',
            [Severity::Error],
        );
    }

    public function testMajorAlarm(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmNotification
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmResource.0 "System/Root1/RFM1/Carrier1"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeId.0 "remote-fade-low"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeQualifier.0 "default"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmLastStateChange.0 2026-08-05,15:38:51.0,-4:0
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmPerceivedSeverity.0 major
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmText.0 "Remote Fade margin Low"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmEventType.0 qualityOfServiceAlarm
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCause.0 thresholdCrossed
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCauseString.0 "thresholdCrossed"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTrendIndication.0 moreSevere
TRAP,
            'Aviat alarm on System/Root1/RFM1/Carrier1: Remote Fade margin Low (severity: major) [cause: thresholdCrossed]',
            'Could not handle aviatIetfAlarmNotification major',
            [Severity::Warning],
        );
    }

    public function testMinorAlarm(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmNotification
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmResource.0 "System/Root1"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeId.0 "ntp-lost-connection-to-server"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeQualifier.0 "default"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmLastStateChange.0 2026-08-05,15:38:51.0,-4:0
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmPerceivedSeverity.0 minor
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmText.0 "All configured NTP peers are unreachable"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmEventType.0 communicationsAlarm
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCause.0 timingProblem
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCauseString.0 "timingProblem"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTrendIndication.0 noChange
TRAP,
            'Aviat alarm on System/Root1: All configured NTP peers are unreachable (severity: minor) [cause: timingProblem]',
            'Could not handle aviatIetfAlarmNotification minor',
            [Severity::Notice],
        );
    }

    public function testWarningAlarm(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmNotification
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmResource.0 "System/Root1/RFM1/Carrier1"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeId.0 "xpd-too-low"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeQualifier.0 "default"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmLastStateChange.0 2026-08-05,15:38:51.0,-4:0
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmPerceivedSeverity.0 warning
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmText.0 "Cross-Polarization Discrimination (XPD) less than 13 dB"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmEventType.0 qualityOfServiceAlarm
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCause.0 thresholdCrossed
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCauseString.0 "thresholdCrossed"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTrendIndication.0 noChange
TRAP,
            'Aviat alarm on System/Root1/RFM1/Carrier1: Cross-Polarization Discrimination (XPD) less than 13 dB (severity: warning) [cause: thresholdCrossed]',
            'Could not handle aviatIetfAlarmNotification warning',
            [Severity::Notice],
        );
    }

    public function testClearedAlarm(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmNotification
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmResource.0 "System/Root1/RFM1/Carrier1"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeId.0 "remote-fade-low"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeQualifier.0 "default"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmLastStateChange.0 2026-08-05,15:44:02.0,-4:0
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmPerceivedSeverity.0 cleared
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmText.0 "Remote Fade margin Low"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmEventType.0 qualityOfServiceAlarm
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCause.0 thresholdCrossed
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCauseString.0 "thresholdCrossed"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTrendIndication.0 lessSevere
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmLastClearedTime.0 2026-08-05,15:44:02.0,-4:0
TRAP,
            'Aviat alarm on System/Root1/RFM1/Carrier1: Remote Fade margin Low (severity: cleared) [cause: thresholdCrossed]',
            'Could not handle aviatIetfAlarmNotification cleared',
            [Severity::Ok],
        );
    }

    /**
     * ItuPerceivedSeverity may arrive as the raw integer rather than the enum label
     * depending on snmptrapd's output options. major(4) must still map to Warning.
     * Note the ordering is inverted - critical(3) is more severe than warning(6) - so
     * this cannot be a numeric comparison.
     */
    public function testNumericSeverity(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmNotification
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmResource.0 "System/Root1/RFM1"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeId.0 "tx-power-degraded"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTypeQualifier.0 "default"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmLastStateChange.0 2026-08-05,15:38:51.0,-4:0
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmPerceivedSeverity.0 4
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmText.0 "Transmit power degraded"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmEventType.0 equipmentAlarm
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCause.0 transmitterFailure
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCauseString.0 "transmitterFailure"
AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmTrendIndication.0 moreSevere
TRAP,
            'Aviat alarm on System/Root1/RFM1: Transmit power degraded (severity: 4) [cause: transmitterFailure]',
            'Could not handle aviatIetfAlarmNotification with numeric severity',
            [Severity::Warning],
        );
    }
}
