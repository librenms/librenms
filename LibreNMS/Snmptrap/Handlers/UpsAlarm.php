<?php

/**
 * UpsAlarm.php
 *
 * Common utility class for decoding RFC 1628 UPS-MIB well-known alarms carried
 * in upsTrapAlarmEntryAdded / upsTrapAlarmEntryRemoved notifications.
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

namespace LibreNMS\Snmptrap\Handlers;

use LibreNMS\Enum\Severity;
use LibreNMS\Snmptrap\Trap;

class UpsAlarm
{
    /**
     * UPS-MIB upsWellKnownAlarms (RFC 1628), keyed by their OBJECT-IDENTITY index.
     */
    private const ALARMS = [
        1 => ['upsAlarmBatteryBad', 'One or more batteries have been determined to require replacement.', Severity::Error],
        2 => ['upsAlarmOnBattery', 'The UPS is drawing power from the batteries.', Severity::Warning],
        3 => ['upsAlarmLowBattery', 'The remaining battery run-time is less than or equal to the configured low battery time.', Severity::Error],
        4 => ['upsAlarmDepletedBattery', 'The UPS will be unable to sustain the present load when and if utility power is lost.', Severity::Error],
        5 => ['upsAlarmTempBad', 'A temperature is out of tolerance.', Severity::Error],
        6 => ['upsAlarmInputBad', 'An input condition is out of tolerance.', Severity::Error],
        7 => ['upsAlarmOutputBad', 'An output condition (other than output overload) is out of tolerance.', Severity::Error],
        8 => ['upsAlarmOutputOverload', 'The output load exceeds the UPS output capacity.', Severity::Error],
        9 => ['upsAlarmOnBypass', 'The bypass is presently engaged on the UPS.', Severity::Warning],
        10 => ['upsAlarmBypassBad', 'The bypass is out of tolerance.', Severity::Error],
        11 => ['upsAlarmOutputOffAsRequested', 'The UPS has shut down as requested; the output is off.', Severity::Notice],
        12 => ['upsAlarmUpsOffAsRequested', 'The entire UPS has shut down as commanded.', Severity::Notice],
        13 => ['upsAlarmChargerFailed', 'An uncorrected problem has been detected within the UPS charger subsystem.', Severity::Error],
        14 => ['upsAlarmUpsOutputOff', 'The output of the UPS is in the off state.', Severity::Warning],
        15 => ['upsAlarmUpsSystemOff', 'The UPS system is in the off state.', Severity::Warning],
        16 => ['upsAlarmFanFailure', 'The failure of one or more fans in the UPS has been detected.', Severity::Error],
        17 => ['upsAlarmFuseFailure', 'The failure of one or more fuses has been detected.', Severity::Error],
        18 => ['upsAlarmGeneralFault', 'A general fault in the UPS has been detected.', Severity::Error],
        19 => ['upsAlarmDiagnosticTestFailed', 'The result of the last diagnostic test indicates a failure.', Severity::Error],
        20 => ['upsAlarmCommunicationsLost', 'A problem has been encountered in the communications between the agent and the UPS.', Severity::Error],
        21 => ['upsAlarmAwaitingPower', 'The UPS output is off and the UPS is awaiting the return of input power.', Severity::Warning],
        22 => ['upsAlarmShutdownPending', 'A shutdown-after-delay countdown is underway.', Severity::Warning],
        23 => ['upsAlarmShutdownImminent', 'The UPS will turn off power to the load in less than 5 seconds.', Severity::Error],
        24 => ['upsAlarmTestInProgress', 'A UPS test is in progress.', Severity::Info],
    ];

    protected function describe(Trap $trap): string
    {
        [$name, $description] = $this->lookup($trap);

        return $description === null ? $name : "$name: $description";
    }

    protected function getSeverity(Trap $trap): Severity
    {
        return $this->lookup($trap)[2];
    }

    /**
     * @return array{0: string, 1: ?string, 2: Severity}
     */
    private function lookup(Trap $trap): array
    {
        $descr = $trap->getOidData($trap->findOid(['UPS-MIB::upsAlarmDescr', 'mib-2.33.1.6.2.1.2']));

        foreach (self::ALARMS as $index => $alarm) {
            // the agent may deliver the alarm identity fully translated (name) or not (trailing numeric index)
            if (str_contains($descr, $alarm[0]) || str_ends_with($descr, ".$index")) {
                return $alarm;
            }
        }

        return [$descr !== '' ? $descr : 'Unknown UPS alarm', null, Severity::Warning];
    }
}
