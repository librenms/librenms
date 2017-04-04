<?php

//SNMPv2-SMI::enterprises.6527.3.1.2.1.1.5.0 = Gauge32: 9 - sgiSwMajorVersion
//SNMPv2-SMI::enterprises.6527.3.1.2.1.1.6.0 = Gauge32: 0 - sgiSwMinorVersion
//SNMPv2-SMI::enterprises.6527.3.1.2.1.1.7.0 = STRING: "R3" - sgiSwVersionModifier

$majorVersion = trim(snmp_get($device, '1.3.6.1.4.1.6527.3.1.2.1.1.5.0', '-OQv', '', ''), '" ');
$minorVersion = trim(snmp_get($device, '1.3.6.1.4.1.6527.3.1.2.1.1.6.0', '-OQv', '', ''), '" ');
$versionModifier = trim(snmp_get($device, '1.3.6.1.4.1.6527.3.1.2.1.1.7.0', '-OQv', '', ''), '" ');

$version = 'v' . $majorVersion . '.' . $minorVersion . '.' . $versionModifier;

//SNMPv2-MIB::sysDescr.0 = STRING: TiMOS-B-9.0.R3 both/hops Nokia SAS-Sx 48Tp4SFP+ (PoE) 7210 Copyright (c) 2000-2017 Nokia.
//All rights reserved. All use subject to applicable license agreements.
//Built on Thu Jan 5 11:01:16 IST 2017 by builder in /home/builder/9.0B1/R3/panos/main

$pattern = "~(cpm|both)\/(hops64|hops|x86_64) (?'hardware'.*)\sCopyright~";
preg_match($pattern, $poll_device['sysDescr'], $matches);

if ($matches['hardware']) {
    $hardware = $matches['hardware'];
}
