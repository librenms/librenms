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

echo 'States: ';

$include_dir = 'includes/discovery/sensors/states';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['states']);

check_valid_sensors($device, 'states', $valid['sensor']);

echo "\n";
