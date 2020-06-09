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

$data1 = explode(' ', $device['sysDescr']);

if (isset($data1[1])) {
    $hardware = $data1[1];
}

if (isset($data1[5])) {
    $data2 = explode('.', $data1[5]);
    $version = $data2[1].".".$data2[2].".".$data2[3].".".$data2[4];
}
