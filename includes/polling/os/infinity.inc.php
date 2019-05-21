<?php
/*
 * LibreNMS LigoWave Infinity OS information module
 *
 * Copyright (c) 2015 Mike Rostermund <mike@kollegienet.dk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$data = explode(' ', $device['sysDescr']);
$hardware = $data[0] . ' ' . $data[1];
$version = $data[2];
unset($data);
