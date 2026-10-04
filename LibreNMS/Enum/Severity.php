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
}
