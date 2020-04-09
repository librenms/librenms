<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2020 PipoCanaja
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

preg_match("/^(.*)\[([^\]]+)\], Bootcode\[([^\]]+)\], Hardware\[([^\]]+)\]/i", $device['sysDescr'], $matches);
$version = $matches[1] . ' ' . $matches[2] . ', Bootcode ' . $matches[3];
$hardware = $matches[4];
