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

$hardware = snmp_get($device, 'nbsCmmcChassisModel.1', '-Ovqs', 'NBS-CMMC-MIB', $config['install_dir'].'/mibs/mrv');
$version = snmp_get($device, 'nbsCmmcSysFwVers.0', '-Ovqs', 'NBS-CMMC-MIB', $config['install_dir'].'/mibs/mrv');
$serial = snmp_get($device, 'nbsCmmcChassisSerialNum.1', '-Ovqs', 'NBS-CMMC-MIB', $config['install_dir'].'/mibs/mrv');
$features       = '';
