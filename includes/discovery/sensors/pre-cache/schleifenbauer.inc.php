<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Martijn Schmidt <martijn.schmidt@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'sdbMgmtCtrlDevUnitAddress ';
$pre_cache['sdbMgmtCtrlDevUnitAddress'] = current(snmpwalk_array_num($device, '.1.3.6.1.4.1.31034.12.1.1.1.2.4.1.2', 1));

echo 'sdbDevIdSerialNumber ';
$pre_cache['sdbDevIdSerialNumber']      = current(snmpwalk_array_num($device, '.1.3.6.1.4.1.31034.12.1.1.2.1.1.1.6', 1));

echo 'sdbDevInActualVoltage ';
$pre_cache['sdbDevInActualVoltage']     = current(snmpwalk_array_num($device, '.1.3.6.1.4.1.31034.12.1.1.2.6.1.1.7', 2));

echo 'sdbDevInActualCurrent ';
$pre_cache['sdbDevInActualCurrent']     = current(snmpwalk_array_num($device, '.1.3.6.1.4.1.31034.12.1.1.2.6.1.1.5', 2));

echo 'sdbDevInMaxAmps ';
$pre_cache['sdbDevInMaxAmps']           = current(snmpwalk_array_num($device, '.1.3.6.1.4.1.31034.12.1.1.2.6.1.1.11', 2));

echo 'sdbDevCfMaximumLoad ';
$pre_cache['sdbDevCfMaximumLoad']       = current(snmpwalk_array_num($device, '.1.3.6.1.4.1.31034.12.1.1.2.2.1.1.6', 1));

echo 'sdbDevInPowerVoltAmpere ';
$pre_cache['sdbDevInPowerVoltAmpere']   = current(snmpwalk_array_num($device, '.1.3.6.1.4.1.31034.12.1.1.2.6.1.1.9', 2));

echo 'sdbDevInKWhTotal ';
$pre_cache['sdbDevInKWhTotal']          = current(snmpwalk_array_num($device, '.1.3.6.1.4.1.31034.12.1.1.2.6.1.1.2', 2));

echo 'sdbDevInPowerFactor ';
$pre_cache['sdbDevInPowerFactor']       = current(snmpwalk_array_num($device, '.1.3.6.1.4.1.31034.12.1.1.2.6.1.1.4', 2));
