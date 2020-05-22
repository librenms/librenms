<?php

// Eaton UPS with management card PN:744-A3983-02

//XUPS-MIB::xupsIdentManufacturer.0 = STRING: "EATON"
//XUPS-MIB::xupsIdentModel.0 = STRING: "Eaton 9PX 1500 RT"
//XUPS-MIB::xupsIdentSoftwareVersion.0 = STRING: "01.20.5072"
//XUPS-MIB::xupsIdentOemCode.0 = INTEGER: 16
//XUPS-MIB::xupsIdent.5.0 = STRING: "9PX1500RT"
//XUPS-MIB::xupsIdent.6.0 = STRING: "PA11K11CJD"
//EATON-OIDS::xupsMIB.14.1.0 = STRING: "Eaton"
//EATON-OIDS::xupsMIB.14.2.0 = STRING: "Eaton Gigabit Network Card"
//EATON-OIDS::xupsMIB.14.3.0 = STRING: "1.7.5"
//EATON-OIDS::xupsMIB.14.4.0 = STRING: "744-A3983-02"
//EATON-OIDS::xupsMIB.14.5.0 = STRING: "G311J11112"


// This add the software version of the UPS and the management card separated by a "/"
$version = trim(snmp_get($device, 'xupsIdentSoftwareVersion.0', '-OQv', 'XUPS-MIB'), '" ');
$version .= ' / '.trim(snmp_get($device, 'xupsMIB.14.3.0', '-OQv', 'EATON-OIDS'), '" ');

// This add the hardware of the UPS and the management card separated by a "/"
$hardware  = trim(snmp_get($device, 'xupsIdentModel.0', '-OQv', 'XUPS-MIB'), '" ');
$hardware  .= ' / '.trim(snmp_get($device, 'xupsMIB.14.4.0', '-OQv', 'EATON-OIDS'), '" ');

// This add the serial of the UPS and the management card separated by a "/"
$serial = trim(snmp_get($device, 'xupsIdent.6.0', '-OQv', 'XUPS-MIB'), '" ');
$serial .= ' / '.trim(snmp_get($device, 'xupsMIB.14.5.0', '-OQv', 'EATON-OIDS'), '" ');


