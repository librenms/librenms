<?php
/*
 * Copyright (c) 2019 David Leselidze <d.l@comcast.net>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (! is_array($aix_cache['aixFileSystem'])) {
    $aix_cache['aixFileSystem'] = snmpwalk_cache_oid($device, 'aixFsTableEntry', [], 'IBM-AIX-MIB');
    d_echo($aix_cache);
}

$entry = $aix_cache['aixFileSystem'][$storage['storage_index']];

$storage['units'] = 1024 * 1024;
$storage['size'] = ($entry['aixFsSize'] * $storage['units']);
$storage['free'] = ($entry['aixFsFree'] * $storage['units']);
$storage['used'] = ($storage['size'] - $storage['free']);
