<?php
/*
 * LibreNMS Cisco Small Business OS information module
 *
 * Copyright (c) 2015 Mike Rostermund <mike@kollegienet.dk>
 * Copyright (c) 2019 PipoCanaja (github.com/pipocanaja)
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$data = snmp_get_multi($device, ['genGroupHWVersion.0', 'rlPhdUnitGenParamModelName.1', 'rlPhdUnitGenParamHardwareVersion.1', 'rlPhdUnitGenParamSerialNum.1', 'rlPhdUnitGenParamSoftwareVersion.1', 'rlPhdUnitGenParamFirmwareVersion.1'], '-OQUs', 'CISCOSB-DEVICEPARAMS-MIB:CISCOSB-Physicaldescription-MIB');

$hardware = $data['1']['rlPhdUnitGenParamHardwareVersion'] . " " . $data['1']['rlPhdUnitGenParamSoftwareVersion'];

$serial = $data['1']['rlPhdUnitGenParamSerialNum'];

$hwversion = $data['0']['genGroupHWVersion'];
if (! $hwversion) {
    $hwversion = $data['1']['rlPhdUnitGenParamHardwareVersion'];
}
if ($device['sysObjectID'] == '.1.3.6.1.4.1.9.6.1.89.26.1') {
    $hardware = 'SG220-26';
} else {
    $hardware = str_replace(' ', '', $data['1']['rlPhdUnitGenParamModelName']);
}
if ($hwversion) {
    $hardware .= " " . $hwversion;
}

$version  = 'Software ' . $data['1']['rlPhdUnitGenParamSoftwareVersion'];
$boot = $data['0']['rndBaseBootVersion'];
$firmware = $data['1']['rlPhdUnitGenParamFirmwareVersion'];
if ($boot) {
    $version = "$version, Bootldr $boot";
}
if ($firmware) {
    $version = "$version, Firmware $firmware";
}

$features = $data['1']['rlPhdUnitGenParamServiceTag'];
