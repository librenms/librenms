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

if (starts_with($sysDescr, array('Cambium PTP 50650', 'PTP250', 'Cambium'))) {
    $os = 'cambium';
} elseif (starts_with($sysObjectId, '.1.3.6.1.4.1.17713.21')) {
    $os = 'cambium';
}
