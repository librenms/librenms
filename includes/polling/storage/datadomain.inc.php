<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 ACL
 * @author     Abel Laura <abel.laura@gmail.com>
*/

if (!is_array($storage_cache['ddos-storage'])) {
    $storage_cache['ddos-storage'] = snmpwalk_cache_oid($device, 'fileSystemSpaceTable', null, 'DATA-DOMAIN-MIB', 'datadomain');
    d_echo($storage_cache);
}

$iind = 0;
$storage_cache_temp = array();

d_echo($storage);

foreach ($storage_cache['ddos-storage'] as $index => $ventry) {
    if (!array_key_exists('fileSystemResourceName', $ventry)) {
        continue;
    }
    if (is_int($index)) {
        $iind = $index;
    } else {
        $arrindex = explode(".", $index);
        $iind = (int)(end($arrindex))+0;
    }
    if (is_int($iind)) {
        $storage_cache_temp[$iind] = $ventry;
    }
}
d_echo($storage_cache_temp);

$entry = $storage_cache_temp[$storage['storage_index']];

$storage['units']       = 1073741824;
$storage['size']        = $entry['fileSystemSpaceSize'] * $storage['units'];
$storage['free']        = $entry['fileSystemSpaceAvail'] * $storage['units'];
$storage['used']        = $entry['fileSystemSpaceUsed'] * $storage['units'];
