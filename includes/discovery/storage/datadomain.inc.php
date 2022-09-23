<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2019 ACL
 * @author     Abel Laura <abel.laura@gmail.com>
*/

$ddos_storage = snmpwalk_cache_oid($device, 'fileSystemSpaceTable', null, 'DATA-DOMAIN-MIB', 'datadomain');
if (is_array($ddos_storage)) {
    foreach ($ddos_storage as $index => $storage) {
        $fstype = $storage['fileSystemResourceTier'];
        $descr = $storage['fileSystemResourceName'];
        $units = 1073741824;
        $total = $storage['fileSystemSpaceSize'] * $units;
        $used = $storage['fileSystemSpaceUsed'] * $units;
        if ($descr == '/data: post-comp') {
            discover_storage($valid_storage, $device, $index, $fstype, 'datadomain', $descr, $total, $units, $used);
        }
    }
}
unset($fstype, $descr, $total, $used, $units, $ddos_storage);
