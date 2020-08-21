<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

preg_match_all('/Poseidon [0-9]+|[0-9]+.[0-9]+.[0-9]/', $device['sysDescr'], $matches);
$hardware = $matches[0][0];
$version = $matches[0][1];
