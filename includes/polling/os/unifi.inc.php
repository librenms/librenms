<?php

if ($sysmodel = snmp_get($device, 'unifiApSystemModel.0', '-Osqnv', 'UBNT-UniFi-MIB')) {
    $hardware = $sysmodel;
}

if ($sysver = snmp_get($device, 'unifiApSystemVersion.0', '-Osqnv', 'UBNT-UniFi-MIB')) {
    $version = $sysver;
}
