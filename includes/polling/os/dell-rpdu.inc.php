<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
 
$dell_rpdu_data = snmp_get_multi_oid($device, ['rPDUIdentSerialNumberD.1', 'rPDUIdentModelNumberD.1', 'rPDUIdentHardwareRevD.1', 'rPDUIdentFirmwareRevD.1'], '-OUQs', 'DellrPDU-MIB');

$hardware = $dell_rpdu_data['rPDUIdentModelNumberD.1'] . ' ' . $dell_rpdu_data['rPDUIdentHardwareRevD.1'] ;
$serial = $dell_rpdu_data['rPDUIdentSerialNumberD.1'];
$version = $dell_rpdu_data['rPDUIdentFirmwareRevD.1'];

unset($dell_rpdu_data);
