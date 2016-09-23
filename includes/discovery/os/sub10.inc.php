<?php
/*
 * LibreNMS Sub10 OS information module
 */

if (str_contains($sysObjectId, '.1.3.6.1.4.1.39003')) {
    $os = 'sub10';
}
