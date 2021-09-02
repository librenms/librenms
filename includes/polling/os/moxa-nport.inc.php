<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Johan Zaxmy <johan@zaxmy.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// SNMPv2-MIB::sysDescr.0 = STRING: NP5610-16
// SNMPv2-MIB::sysObjectID.0 = OID: SNMPv2-SMI::enterprises.8691.2.7
// DISMAN-EVENT-MIB::sysUpTimeInstance = Timeticks: (50870289) 5 days, 21:18:22.89

$hardware = $device['sysDescr'];
