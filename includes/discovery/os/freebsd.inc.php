<?php

if (!$os) {
    if (strstr($sysDescr, 'Voswall')) {
        $os = 'voswall';
    } //end if
    elseif (strstr($sysDescr, 'm0n0wall')) {
        $os = 'monowall';
    } // Ditto
    elseif (strstr($sysDescr, 'pfSense')) {
        $os = 'pfsense';
    } elseif (strstr($sysDescr, 'FreeBSD')) {
        $os = 'freebsd';
    } // It's FreeBSD!
}
