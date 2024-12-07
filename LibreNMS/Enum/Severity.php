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

    public function toLogLevel(): string
    {
        return match ($this->value) {
            1,2 => 'info',
            3 => 'notice',
            4 => 'warning',
            5 => 'error',
            default => 'debug',
        };
    }
}
