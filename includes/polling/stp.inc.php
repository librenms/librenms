<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Vitali Kari <vitali.kari@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * Based on IEEE-802.1D-2004, (STP, RSTP)
 * needs RSTP-MIB
 */

use LibreNMS\OS;

if (! $os instanceof OS) {
    $os = OS::make($device);
}

(new \LibreNMS\Modules\Stp())->poll($os, app('Datastore'));
