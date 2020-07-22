<?php

/*
 * LibreNMS OS Polling module for Asentria
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$asentsysdescr    = $device['sysDescr'];
$version          = preg_replace('/^\s*(\S+\s+\S+\s+)/', '', $asentsysdescr);
$hardware         = preg_match('/^\S+\s+\d+\s+/', $asentsysdescr, $matches);
$hardware         = trim($matches[0]);

unset(
    $asentsysdescr,
    $matches
);
