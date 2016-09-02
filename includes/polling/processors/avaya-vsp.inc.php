<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Daniel Cox <danielcoxman@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// Avaya VSP cpu w/ VOSS
// rcKhiSlotCpuCurrentUtil  1.3.6.1.4.1.2272.1.85.10.1.1.2.1
// rcKhiSlotCpu5MinAve      1.3.6.1.4.1.2272.1.85.10.1.1.3.1

$proc = trim(snmp_get($device, "1.3.6.1.4.1.2272.1.85.10.1.1.3.1", "-Ovq"), '"');

if (is_numeric($usage)) {
    $proc = ($usage * 100);
}
