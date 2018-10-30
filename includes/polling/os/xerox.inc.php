<?php

// ...253.8.51.1.2.1.20.1 = STRING: "MFG:Xerox;CMD:Adobe PostScript 3,PCL;MDL:Phaser 4510N;CLS:Printer;DES:Xerox Phaser 4510 Laser Printer, PostScript 3, Letter/A4 Size"
// ...253.8.51.1.2.1.20.1 = STRING: "MFG:Xerox;CMD:Adobe PostScript 3,PCL;MDL:ColorQube 8880DN;CLS:Printer;DES:Xerox ColorQube 8580 & 8880, Color Printer,PostScript 3,Letter/A4 Size "
// ...253.8.51.1.2.1.20.1 = STRING: "MFG:Xerox; CMD:PCL, PJL, POSTSCRIPT; MDL:WorkCentre 7535; CLS:Printer; Des:Xerox WorkCentre 7535"
$xinfo = explode(';', trim(snmp_get($device, '1.3.6.1.4.1.253.8.51.1.2.1.20.1', '-OQv', '', ''), '" '));

foreach ($xinfo as $xi) {
    list($key,$value)        = explode(':', trim($xi));
    $xerox[strtoupper($key)] = $value;
}

list($hardware) = explode(',', $xerox['DES']);

// SNMPv2-SMI::enterprises.236.11.5.1.1.1.1.0 = STRING: "Xerox Phaser 3200MFP"
// SNMPv2-SMI::enterprises.236.11.5.1.1.1.2.0 = STRING: "1.15"
if ($hardware == '') {
    $hardware = trim(snmp_get($device, '1.3.6.1.4.1.236.11.5.1.1.1.1.0', '-OQv', '', ''), '" ');
}

$version = trim(snmp_get($device, '1.3.6.1.4.1.236.11.5.1.1.1.2.0', '-OQv', '', ''), '" ');
$serial = trim(snmp_get($device, '1.3.6.1.2.1.43.5.1.1.17.1', '-OQv', '', ''), '" ');
