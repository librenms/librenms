<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class VsolDataAlarmTrap implements SnmptrapHandler
{
    // V-SOL alarm type IDs mapped to human-readable names
    private const ALARM_NAMES = [
        1 => 'fan', 5 => 'port-updown', 7 => 'pon-deregister',
        10 => 'pon-txpower-high', 11 => 'pon-txpower-low',
        18 => 'pon-los', 19 => 'onu-deregister', 20 => 'onu-link-lost',
        21 => 'onu-illegal-register', 22 => 'onu-auth-failed',
        25 => 'onu-critical-event', 26 => 'onu-dying-gasp',
        27 => 'onu-link-fault', 41 => 'onu-register',
        43 => 'onu-auth-success', 47 => 'onu-pon-rxpower-high',
        48 => 'onu-pon-rxpower-low', 49 => 'onu-pon-txpower-high',
        50 => 'onu-pon-txpower-low', 84 => 'rogue-onu',
    ];

    public function handle(Device $device, Trap $trap): void
    {
        $pon = $trap->getOidData($trap->findOid('V1600GSwitch::dataPon'));
        $onu = $trap->getOidData($trap->findOid('V1600GSwitch::dataOnu'));
        $alarmLevel = $trap->getOidData($trap->findOid('V1600GSwitch::dateAlarmLevel'));
        $alarmTypeId = (int) $trap->getOidData($trap->findOid('V1600GSwitch::dataAlarmType'));
        $value = $trap->getOidData($trap->findOid('V1600GSwitch::dataValue'));

        $alarmName = self::ALARM_NAMES[$alarmTypeId] ?? "alarm-$alarmTypeId";

        // Build location string
        $location = '';
        if (! empty($pon) && $pon !== 'dataPon') {
            $location .= "PON $pon";
        }
        if (! empty($onu) && $onu !== 'dataOnu') {
            $location .= " ONU $onu";
        }
        $location = trim($location);

        $message = $alarmName;
        if ($location) {
            $message .= " ($location)";
        }
        if (! empty($value) && $value !== 'dataValue') {
            $message .= " value=$value";
        }

        $severity = match ($alarmLevel) {
            'criterr', 'alert', 'emerg' => Severity::Error,
            'major' => Severity::Error,
            'warning' => Severity::Warning,
            'notice' => Severity::Notice,
            'info' => Severity::Ok,
            default => Severity::Info,
        };

        $trap->log($message, $severity, 'trap');
    }
}
