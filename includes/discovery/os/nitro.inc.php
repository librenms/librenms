<?php

// MaAfee SIEM Nitro
if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.23128.1000.1.1')) {
        $os = 'nitro';
    } elseif (strstr($sysObjectId, '.1.3.6.1.4.1.23128.1000.3.1')) {
        $os = 'nitro';
    } elseif (strstr($sysObjectId, '.1.3.6.1.4.1.23128.1000.7.1')) {
        $os = 'nitro';
    } elseif (strstr($sysObjectId, '.1.3.6.1.4.1.23128.1000.11.1')) {
        $os = 'nitro';
    }
}
