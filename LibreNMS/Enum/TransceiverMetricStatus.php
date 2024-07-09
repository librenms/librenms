<?php

namespace LibreNMS\Enum;

enum TransceiverMetricStatus: int
{
    case Unknown = 0;
    case Ok = 1;
    case ExceededMinWarning = 2;
    case ExceededMaxWarning = 3;
    case ExceededMinCritical = 4;
    case ExceededMaxCritical = 5;

    public function asSeverity(): Severity
    {
        return match ($this) {
            self::ExceededMinCritical,self::ExceededMaxCritical => Severity::Error,
            self::ExceededMinWarning,self::ExceededMaxWarning => Severity::Warning,
            self::Ok => Severity::Ok,
            default => Severity::Unknown,
        };
    }
}
