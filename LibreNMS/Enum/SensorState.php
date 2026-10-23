<?php

namespace LibreNMS\Enum;

enum SensorState: int
{
    case Ok = 0;
    case Warning = 1;
    case Error = 2;
    case Unknown = 3;

    public static function fromDescr(string $descr): self
    {
        return match ($descr) {
            'On', 'Okay', 'Ok' => self::Ok,
            'Standby', 'Idle', 'Maintenance' => self::Warning,
            'Under', 'Over' => self::Error,
            default => self::Unknown,
        };
    }

    public function toSeverity(): Severity
    {
        return match ($this) {
            self::Ok => Severity::Ok,
            self::Warning => Severity::Warning,
            self::Error => Severity::Error,
            self::Unknown => Severity::Unknown,
        };
    }
}
