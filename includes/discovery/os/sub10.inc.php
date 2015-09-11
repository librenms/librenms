<?php
/*
 * LibreNMS Sub10 OS information module
 */

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.39003')) {
        $os = 'sub10';
    }
}
