<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2018 ACL
 * @author     Abel Laura <abel.laura@gmail.com>
*/

if (! is_array($storage_cache['ddos-storage'])) {
    $storage_cache['ddos-storage'] = snmpwalk_cache_oid($device, 'fileSystemSpaceTable', null, 'DATA-DOMAIN-MIB', 'datadomain');
    d_echo($storage_cache);
}

foreach ($storage_cache['ddos-storage'] as $fsentry) {
    if ($fsentry['fileSystemResourceName'] == '/data: post-comp') {
        $storage['units'] = 1073741824;
        $storage['size'] = $fsentry['fileSystemSpaceSize'] * $storage['units'];
        $storage['free'] = $fsentry['fileSystemSpaceAvail'] * $storage['units'];
        $storage['used'] = $fsentry['fileSystemSpaceUsed'] * $storage['units'];
    }
}
