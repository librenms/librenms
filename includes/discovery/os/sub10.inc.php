<?php
/*
 * LibreNMS Sub10 OS information module
 */

if (str_contains('.1.3.6.1.4.1.39003', $sysObjectId)) {
    $os = 'sub10';
}
