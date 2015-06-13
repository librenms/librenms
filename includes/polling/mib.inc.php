<?php
/*
 * LibreNMS MIB-based polling
 *
 * Copyright (c) 2015 Gear Consulting Pty Ltd <github@libertysys.com.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo("MIB-based polling");
d_echo("\n", ": ");

$devicemib = array($device['sysObjectID'] => "all");
poll_mibs($devicemib, $device, $graphs);

d_echo("Done MIB-based polling");
echo("\n");
?>
