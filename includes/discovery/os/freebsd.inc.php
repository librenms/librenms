<?php

// FreeBSD variants, check for specialized distros first
if (str_contains($sysDescr, 'pfSense')) {
    $os = 'pfsense';
} elseif (str_contains($sysDescr, 'Voswall')) {
    $os = 'voswall';
} elseif (str_contains($sysDescr, 'm0n0wall')) {
    $os = 'monowall';
} elseif (str_contains($sysDescr, 'FreeBSD')) {
    $os = 'freebsd';
}
