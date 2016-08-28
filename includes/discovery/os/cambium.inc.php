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
    if (preg_match('/^Cambium PTP 50650/', $sysDescr)) {
        $os = 'cambium';
    } elseif (preg_match('/^PTP250/', $sysDescr)) {
        $os = 'cambium';
    } elseif (preg_match('/^Cambium/', $sysDescr)) {
        $os = 'cambium';
    } elseif (strstr($sysObjectId, '.1.3.6.1.4.1.17713.21')) {
        $os = 'cambium';
    } elseif (strstr($sysObjectId, 'enterprises.17713.21')) {
        $os = 'cambium';
    }
}
