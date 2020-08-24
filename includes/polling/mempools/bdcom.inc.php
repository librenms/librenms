<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2020 hartred <tumanov@asarta.ru>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$perc = snmp_get($device, ['.1.3.6.1.4.1.3320.3.6.10.1.12.0'], '-OQv', 'NMS-CHASSIS');

if (is_numeric($perc)) {
    $mempool['perc'] = $perc;
    $mempool['used'] = $perc;
    $mempool['total'] = 100;
    $mempool['free'] = 100 - $perc;
}
