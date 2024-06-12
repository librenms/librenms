<?php

namespace LibreNMS\OS;

use LibreNMS\OS;

class BaicellsOd04 extends OS
{
    /**
     * convert hh:mm:ss to minutes (for yaml user_func)
     */
    public static function hhmmss_to_minutes($duration)
    {
        [$h, $m, $s] = explode(':', $duration);

        return (int) $h * 60 + (int) $m + (int) $s / 60;
    }
}
