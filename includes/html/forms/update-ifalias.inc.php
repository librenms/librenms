<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/
header('Content-type: application/json');

$status = 'error';

$descr = $_POST['descr'];
$device_id = $_POST['device_id'];
$ifName = $_POST['ifName'];
$port_id = $_POST['port_id'];

if (! empty($ifName) && is_numeric($port_id)) {
    // We have ifName and  port id so update ifAlias
    if (empty($descr)) {
        $descr = 'repoll';
        // Set to repoll so we avoid using ifDescr on port poll
    }
    if (dbUpdate(['ifAlias'=>$descr], 'ports', '`port_id`=?', [$port_id]) > 0) {
        $device = device_by_id_cache($device_id);
        if ($descr === 'repoll') {
            del_dev_attrib($device, 'ifName:' . $ifName);
            log_event("$ifName Port ifAlias cleared manually", $device, 'interface', 3, $port_id);
        } else {
            set_dev_attrib($device, 'ifName:' . $ifName, 1);
            log_event("$ifName Port ifAlias set manually: $descr", $device, 'interface', 3, $port_id);
        }
        $status = 'ok';
    } else {
        $status = 'na';
    }
}

$response = [
    'status'        => $status,
];
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
