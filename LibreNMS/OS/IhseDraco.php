<?php

namespace LibreNMS\OS;

use LibreNMS\OS;

class IhseDraco extends OS
{
    public static function slotState(int|float|string $value): int
    {
        $status = (int) $value;

        if ($status & 0x80000000) {
            return 5;
        }

        if ($status & 0x04000000) {
            return 3;
        }

        if ($status & 0x40000000) {
            return 4;
        }

        if (($status & 0x07) !== 0x07) {
            return $status === 0 ? 0 : 6;
        }

        return ($status & 0x08) ? 2 : 1;
    }

    public static function portState(int|float|string $value): int
    {
        $status = (int) $value;

        if ($status & 0x8000) {
            return 3;
        }

        if (($status & 0x7f) === 0x7f) {
            return 2;
        }

        if (($status & 0x07) === 0x07) {
            return 1;
        }

        return $status === 0 ? 0 : 4;
    }
}
