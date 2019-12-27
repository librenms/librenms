<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2019 hartred <tumanov@asarta.ru>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
 
$r_temp = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.10072.2.20.1.1.1.1.1.8.1', '.1.3.6.1.4.1.10072.2.20.1.1.1.1.1.9.1', '.1.3.6.1.4.1.10072.2.20.1.1.2.1.1.18.1.1'], '-OQUn');
$version   = $r_temp['.1.3.6.1.4.1.10072.2.20.1.1.1.1.1.8.1'];
$hardware  = $r_temp['.1.3.6.1.4.1.10072.2.20.1.1.1.1.1.9.1'];
$serial    = $r_temp['.1.3.6.1.4.1.10072.2.20.1.1.2.1.1.18.1.1'];
