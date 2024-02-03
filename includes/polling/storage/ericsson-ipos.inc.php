<?php
/*
 * Copyright (c) 2024 Rudy Broersma <tozz@kijkt.tv>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($storage['storage_type'] === "eriRouterSRStorage") {
    $data_used = snmpwalk_cache_oid($device, 'eriRouterSRStorageUtilization.' . $storage['storage_index'], [], 'ERICSSON-ROUTER-SYS-RESOURCES-MIB')[$storage['storage_index']];
    $data_size = snmpwalk_cache_oid($device, 'eriRouterSRStorageSize.' . $storage['storage_index'], [], 'ERICSSON-ROUTER-SYS-RESOURCES-MIB')[$storage['storage_index']];

    $storage['size'] = $data_size['eriRouterSRStorageSize'];
    $storage['used'] = round(($storage['storage_size'] / 100) * $data_used['eriRouterSRStorageUtilization'], 0);
    $storage['free'] = round($storage['storage_size'] - $used, 0);
    $storage['units'] = 1024;
}
