<?php
$version  = trim(snmp_get($device, '1.3.6.1.4.1.1588.2.1.1.1.1.6.0', '-Ovq'), '"');
$gethardware = trim(snmp_get($device, 'SNMPv2-SMI::mib-2.75.1.1.4.1.3.1', '-Ovq'), '"');
$revboard = str_replace("SNMPv2-SMI::enterprises.1588.2.1.1.", "", $gethardware);
if (strpos($revboard, ".") !== false) {
    $hardware = rewrite_brocade_fc_switches(strstr(str_replace($revboard, "", $gethardware), ".", true));
} else {
    $hardware = rewrite_brocade_fc_switches($revboard);
}
