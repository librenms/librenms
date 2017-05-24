<?php

/*
 * LibreNMS OS Polling module for the MRV® OptiDriver® Optical Transport Platform
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$multi_get_array = snmp_get_multi($device, 'nbsCmmcChassisModel.1 nbsCmmcSysFwVers.0 nbsCmmcChassisSerialNum.1', '-Ovqs', 'NBS-CMMC-MIB', $config['install_dir'].'/mibs/mrv');
$hardware = $multi_get_array[1]['NBS-CMMC-MIB::nbsCmmcChassisModel'];
$version = $multi_get_array[0]['NBS-CMMC-MIB::nbsCmmcSysFwVers'];
$serial = $multi_get_array[1]['NBS-CMMC-MIB::nbsCmmcChassisSerialNum'];
$features       = '';
unset($multi_get_array);
