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

if ($device['os'] == 'pbn') {
    echo 'Pre-cache PBN: ';

    $pbn_oids = array();
    echo 'Caching OIDs:';

    $pbn_oids = snmpwalk_cache_multi_oid($device, 'ifSfpParameterTable', array(), 'NMS-IF-MIB', 'pbn');
}
