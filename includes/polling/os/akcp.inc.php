<?php

$hardware = trim(snmp_get($device, "1.3.6.1.4.1.3854.1.1.6.0", "-OQv", "", ""), '"');
$hardware .= ' ' . trim(snmp_get($device, "1.3.6.1.4.1.3854.1.1.8.0", "-OQv", "", ""), '" ');


if (empty(trim($hardware))) {
    preg_match('/SP\d+/', $device['sysDescr'], $matches);
    $hardware = $matches[0];
}
