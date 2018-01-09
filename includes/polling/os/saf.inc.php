<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/

preg_match('/ radio;(.*) v([0-9.]*);.*;SN: ([0-9]+);/', $device['sysDescr'], $matches);

$hardware = $matches[1];
$version = $matches[2];
$serial = $matches[3];
