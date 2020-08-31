<?php
/*
 * LibreNMS QuantaStor temperature module
 *
 * Copyright (c) 2020 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$infos = snmp_get_multi($device, 'QUANTASTOR-SYS-STATS::storageSystem-ServiceVersion.0 QUANTASTOR-SYS-STATS::hwEnclosure-Vendor.0 QUANTASTOR-SYS-STATS::hwEnclosure-Model.0 QUANTASTOR-SYS-STATS::storageSystem-SerialNumber.0', '-OQUs', '+QUANTASTOR-SYS-STATS', 'quantastor');


$version = $infos[0]['storageSystem-ServiceVersion'];
$hardware = $infos[0]['hwEnclosure-Vendor'] . ' ' . $infos[0]['hwEnclosure-Model'];
$serial = $infos[0]['storageSystem-SerialNumber'];
