<?php

namespace App\Enum;

enum AlertLogSeverity: int
{
    case AllSeverities = 1;
    case WarningAndAbove = 2;
    case CriticalOnly = 3;
    case OkOnly = 4;
    case WarningOnly = 5;

    /**
     * Get the severity strings for database filtering
     *
     * @return array|string
     */
    public function getSeverities(): array|string
    {
        return match ($this) {
            self::AllSeverities => ['ok', 'warning', 'critical'],
            self::WarningAndAbove => ['warning', 'critical'],
            self::CriticalOnly => 'critical',
            self::OkOnly => 'ok',
            self::WarningOnly => 'warning',
        };
    }
}
