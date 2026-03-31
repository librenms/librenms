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

    private const OID_BASE = '.1.3.6.1.4.1.37950.1.1.5.10.13.2';

    public function handle(Device $device, Trap $trap): void
    {
        $pon = $trap->getOidData(self::OID_BASE . '.2.0');
        $onu = $trap->getOidData(self::OID_BASE . '.3.0');
        $alarmLevel = (int) $trap->getOidData(self::OID_BASE . '.9.0');
        $alarmTypeId = (int) $trap->getOidData(self::OID_BASE . '.11.0');
        $value = $trap->getOidData(self::OID_BASE . '.10.0');

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
            4, 5 => Severity::Error,      // critical/major
            3 => Severity::Warning,        // minor
            2 => Severity::Notice,         // warning
            1 => Severity::Ok,             // cleared
            default => Severity::Info,
        };

        $trap->log($message, $severity, 'trap');
    }
}
