<?php
$serial = snmp_getnext($device, ".1.3.6.1.4.1.32285.11.1.1.2.1.1.1.16", "-OQv");

preg_match('/hardware version:V([^;]+);software version:V([^;]+);/', $device['sysDescr'], $tv_matches);
if (isset($tv_matches[2])) {
    $version = $tv_matches[2];
}
if (isset($tv_matches[1])) {
    $hardware = $tv_matches[1];
} else {
    $hardware = snmp_getnext($device, ".1.3.6.1.4.1.32285.11.1.1.2.1.1.1.18", "-OQv");
}
