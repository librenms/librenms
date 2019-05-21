<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Aldemir Akpinar <aldemir.akpinar@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$oids = array(
    'hardware' => $device['sysObjectID'].'.1.2.0',
    'version' => $device['sysObjectID'].'.1.4.0',
    'serial' => $device['sysObjectID'].'.1.78.0',
);

$os_data = snmp_get_multi_oid($device, $oids);

foreach ($oids as $var => $oid) {
    $$var = trim($os_data[$oid], '"');
}

unset($oids, $os_data);
