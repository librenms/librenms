<?php

namespace LibreNMS\Enum;

enum Severity: int
{
    case Unknown = 0;
    case Ok = 1;
    case Info = 2;
    case Notice = 3;
    case Warning = 4;
    case Error = 5;

    /**
     * Nagios alerting states stored in state_translations.state_generic_value.
     */
    public const NAGIOS_ALERTING_OK = 0;
    public const NAGIOS_ALERTING_WARNING = 1;
    public const NAGIOS_ALERTING_ERROR = 2;
    public const NAGIOS_ALERTING_UNKNOWN = 3;

    /**
     * Convert the severity string of an alert rule (ok, warning, critical) to a Severity.
     */
    public static function fromAlertRule(?string $severity): self
    {
        return match ($severity) {
            'ok' => self::Ok,
            'warning' => self::Warning,
            'critical' => self::Error,
            default => self::Unknown,
        };
    }

    /**
     * Map a state sensor description to a severity.
     */
    public static function fromSensorDescr(string $descr): self
    {
        return match ($descr) {
            'On', 'Okay', 'Ok' => self::Ok,
            'Standby', 'Idle', 'Maintenance' => self::Warning,
            'Under', 'Over' => self::Error,
            // APC PowerNet-MIB output relay/outlet commands
            'immediateCloseEMS', 'immediateOnEMS' => self::Error,
            'immediateOpenEMS', 'immediateOffEMS' => self::Ok,
            default => self::Unknown,
        };
    }

    /**
     * Convert a generic state value (state_translations.state_generic_value) to a severity.
     */
    public static function fromGenericState(int $generic): self
    {
        return match ($generic) {
            self::NAGIOS_ALERTING_OK => self::Ok,
            self::NAGIOS_ALERTING_WARNING => self::Warning,
            self::NAGIOS_ALERTING_ERROR => self::Error,
            default => self::Unknown,
        };
    }

    /**
     * Convert the severity to the generic state value stored in state_translations.
     */
    public function toGenericState(): int
    {
        return match ($this) {
            self::Ok => self::NAGIOS_ALERTING_OK,
            self::Warning => self::NAGIOS_ALERTING_WARNING,
            self::Error => self::NAGIOS_ALERTING_ERROR,
            default => self::NAGIOS_ALERTING_UNKNOWN,
        };
    }
}
