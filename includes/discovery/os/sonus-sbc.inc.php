<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (!$os) {
    if (starts_with($sysObjectId, array('.1.3.6.1.4.1.2879.1.9.2','.1.3.6.1.4.1.177.15.1.1.1'))) {
        $os = 'sonus-sbc';
    }
}
