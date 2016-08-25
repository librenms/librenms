<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.9.1.2307')) {
        $os = 'primeinfrastructure';
    }
}
