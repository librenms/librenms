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
    $data_used = SnmpQuery::hideMib()->get('ERICSSON-ROUTER-SYS-RESOURCES-MIB::eriRouterSRStorageUtilization.'.$storage['storage_index'])->value();
    $data_size = SnmpQuery::hideMib()->get('ERICSSON-ROUTER-SYS-RESOURCES-MIB::eriRouterSRStorageSize.'.$storage['storage_index'])->value();

    $storage['size'] = $data_size;
    $storage['used'] = round(($storage['storage_size'] / 100) * $data_used, 0);
    $storage['free'] = round($storage['storage_size'] - $used, 0);
    $storage['units'] = 1024;
}
