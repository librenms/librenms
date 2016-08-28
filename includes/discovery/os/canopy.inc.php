<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (!$os) {
    if (preg_match('/^CANOPY/', $sysDescr)) {
        $os = 'canopy';
    } elseif (preg_match('/^CMM/', $sysDescr)) {
        $os = 'canopy';
    }
}
