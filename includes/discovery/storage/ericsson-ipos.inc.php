<?php
/*
 * Copyright (c) 2024 Rudy Broersma <tozz@kijkt.tv>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * eriRouterSRStorageSize is reported in KBytes
 */

if ($device['os'] == 'ericsson-ipos') {
    $eri_filesystem = SnmpQuery::hideMib()->walk('ERICSSON-ROUTER-SYS-RESOURCES-MIB::eriRouterSRStorageTable')->table(1);

    if (is_array($eri_filesystem)) {
        echo 'Ericsson IPOS Filesystem:';
        foreach ($eri_filesystem as $index => $eri_fs) {
            $used = round(($eri_fs['eriRouterSRStorageSize'] / 100) * $eri_fs['eriRouterSRStorageUtilization'], 0);
            discover_storage($valid_storage, $device, $index, 'eriRouterSRStorage', 'ericsson-ipos', $eri_fs['eriRouterSRStorageDescr'], $eri_fs['eriRouterSRStorageSize'], 1024, $used);
        }
    }
    unset($eri_fs);
}
