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

list(,$version,,,$serial,,) = explode(";", $poll_device['sysDescr']);
$hardware = str_replace('"', "", snmp_get($device, 'product.0', '-OQva', 'SAF-IPRADIO', 'saf'));
$version = end(explode(" ", $version));
list(,$serial) = explode(":", $serial);
