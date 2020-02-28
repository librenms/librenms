<?php

/**
 * forcepoint.inc.php
 * LibreNMS mempool discovery module for forcepoint
 */


if ($device['os'] == 'forcepoint-os') {
    echo 'Forcepoint Memory discovery : ';

    $total = snmp_get($device, 'STONESOFT-FIREWALL-MIB::fwMemBytesTotal.0', '-OvQU');
    $used = snmp_get($device, 'STONESOFT-FIREWALL-MIB::fwMemBytesUsed.0', '-OvQU');

    if (is_numeric($used) && is_numeric($total)) {
        discover_mempool($valid_mempool, $device, 0, 'forcepoint-os', 'Memory', '100', null, null);
    }

}
