<?php

namespace LibreNMS\Enum;

enum AlertLogState: int
{
    case Clear = 0;
    case Active = 1;
    case Acknowledged = 2;
    case Worse = 3;
    case Better = 4;
    case Changed = 5;
    case Recovered = 6;

    public function isActive(): bool
    {
        return match ($this) {
            self::Active, self::Worse, self::Better, self::Changed => true,
            default => false,
        };
    }

    public function asSeverity(): Severity
    {
        return match ($this) {
            self::Clear, self::Recovered => Severity::Ok,
            self::Active => Severity::Error,
            self::Acknowledged => Severity::Info,
            self::Worse, self::Changed => Severity::Warning,
            self::Better => Severity::Notice,
        };
    }
}
