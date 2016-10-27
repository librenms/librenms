<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$ceragon_type = snmp_get($device, 'sysObjectID.0', '-mSNMPv2-MIB -Oqv', '');
if (strstr($ceragon_type, '.2281.1.10')) {
    $hardware = 'IP10 Family';
} elseif (strstr($ceragon_type, '.2281.1.20.1.1')) {
    $hardware = 'IP-20N 1RU';
} elseif (strstr($ceragon_type, '.2281.1.20.1.1.2')) {
    $hardware = 'IP-20A 1RU';
} elseif (strstr($ceragon_type, '.2281.1.20.1.1.4')) {
    $hardware = 'IP-20 Evolution LH 1RU';
} elseif (strstr($ceragon_type, '.2281.1.20.1.2')) {
    $hardware = 'IP-20N 2RU';
} elseif (strstr($ceragon_type, '.2281.1.20.1.2.2')) {
    $hardware = 'IP-20A 2RU';
} elseif (strstr($ceragon_type, '.2281.1.20.1.2.4')) {
    $hardware = 'IP-20 Evolution 2RU';
} elseif (strstr($ceragon_type, '.2281.1.20.1.3.1')) {
    $hardware = 'IP-20G';
} elseif (strstr($ceragon_type, '.2281.1.20.1.3.2')) {
    $hardware = 'IP-20GX';
} elseif (strstr($ceragon_type, '.2281.1.20.2.2')) {
    $hardware = 'IP-20C';
} elseif (strstr($ceragon_type, '.2281.1.20.2.2.2')) {
    $hardware = 'IP-20S';
} elseif (strstr($ceragon_type, '.2281.1.20.2.2.3')) {
    $hardware = 'IP-20E (hardware release 1)';
} elseif (strstr($ceragon_type, '.2281.1.20.2.2.4')) {
    $hardware = 'IP-20E (hardware release 2)';
} else {
    $hardware = snmp_walk($device, 'genEquipInventoryCardName', '-mMWRM-RADIO-MIB -Oqv', '');
}
$version = snmp_get($device, 'genEquipMngSwIDUVersionsRunningVersion.1', '-mMWRM-RADIO-MIB -Oqv', '');
$serial = snmp_walk($device, 'genEquipInventorySerialNumber', '-mMWRM-RADIO-MIB -Oqv', '');
$latitude = snmp_walk($device, 'genEquipUnitLatitude', '-mMWRM-RADIO-MIB -Oqv', '');
$longitude = snmp_walk($device, 'genEquipUnitLongitude', '-mMWRM-RADIO-MIB -Oqv', '');

