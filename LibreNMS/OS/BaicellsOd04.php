<?php

namespace LibreNMS\OS;

use LibreNMS\OS;

class BaicellsOd04 extends OS 
{
    /**
     * convert hh:mm:ss to minutes (for yaml user_func)
     */
    function hhmmss_to_minutes($duration)
    {
        list($h, $m, $s) = explode(':', $duration);
        return $h * 60 + $m + $s / 60;
    }
}
