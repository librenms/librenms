<?php

// SNMPv2-MIB::sysDescr.0 = STRING: NRG MP C4500 1.60 / NRG Network Printer C model / NRG Network Scanner C model / NRG Network Facsimile C model
// SNMPv2-MIB::sysDescr.0 = STRING: NRG SP C410DN 1.01 / NRG Network Printer C model
// SNMPv2-MIB::sysDescr.0 = STRING: NRG MP 171 1.01 / NRG Network Printer C model / NRG Network Scanner C model / NRG Network Facsimile C model
$descr = trim($device['sysDescr'], '" ');

$ninfo = trim(substr($descr, 0, strpos($descr, '/')));

$hardware = trim(substr($ninfo, 0, strrpos($ninfo, ' ')));
$version = trim(substr($ninfo, strrpos($ninfo, ' ')));

// SNMPv2-SMI::enterprises.367.3.2.1.2.1.4.0 = STRING: "M6394300657"
// $serial = trim(snmp_get($device, "1.3.6.1.4.1.367.3.2.1.2.1.4.0", "-OQv", "", ""),'" ');
