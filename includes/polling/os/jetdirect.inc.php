<?php

// ...7.0 = STRING: "MFG:Hewlett-Packard;CMD:PJL,MLC,BIDI-ECP,PCL,POSTSCRIPT,PCLXL;MDL:hp LaserJet 1320 series;CLS:PRINTER;DES:Hewlett-Packard LaserJet 1320 series;MEM:9MB;COMMENT:RES=1200x1;"
$jdinfo = explode(';', trim(snmp_get($device, '1.3.6.1.4.1.11.2.3.9.1.1.7.0', '-OQv', '', ''), '" '));

foreach ($jdinfo as $jdi) {
    list($key,$value) = explode(':', $jdi);
    $jetdirect[$key]  = $value;
}

$hardware = $jetdirect['DES'];

if ($hardware == '') {
    $hardware = $jetdirect['DESCRIPTION'];
}

if ($hardware == '') {
    $hardware = $jetdirect['MODEL'];
}

// Strip off useless brand fields
$hardware = str_replace('HP ', '', $hardware);
$hardware = str_replace('Hewlett-Packard ', '', $hardware);
$hardware = str_ireplace(' Series', '', $hardware);
$hardware = ucfirst($hardware);
