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

$status  = 'error';

$speed = mres($_POST['speed']);
$device_id = mres($_POST['device_id']);
$ifName = mres($_POST['ifName']);
$port_id = mres($_POST['port_id']);

if (!empty($ifName) && is_numeric($port_id) && is_numeric($port_id)) {
    // We have ifName and  port id so update ifAlias
    if (empty($speed)) {
        $speed = array('NULL');
        $high_speed = array('NULL');
        // Set to 999999 so we avoid using ifDescr on port poll
    } else {
        $high_speed = $speed / 1000000;
    }
    if (dbUpdate(array('ifSpeed'=>$speed, 'ifHighSpeed'=>$high_speed), 'ports', '`port_id`=?', array($port_id)) > 0) {
        $device = device_by_id_cache($device_id);
        if (is_array($speed)) {
            del_dev_attrib($device, 'ifSpeed:'.$ifName);
            log_event("$ifName Port speed cleared manually", $device, 'interface', 3, $port_id);
        } else {
            set_dev_attrib($device, 'ifSpeed:'.$ifName, 1);
            log_event("$ifName Port speed set manually: $speed", $device, 'interface', 3, $port_id);
            $port_tune = get_dev_attrib($device, 'ifName_tune:'.$ifName);
            $device_tune = get_dev_attrib($device, 'override_rrdtool_tune');
            if ($port_tune == "true" ||
                ($device_tune == "true" && $port_tune != 'false') ||
                (\LibreNMS\Config::get('rrdtool_tune') == "true" && $port_tune != 'false' && $device_tune != 'false')) {
                $rrdfile = get_port_rrdfile_path($device['hostname'], $port_id);
                rrdtool_tune('port', $rrdfile, $speed);
            }
        }
        $status = 'ok';
    } else {
        $status = 'na';
    }
}

$response = array(
    'status'        => $status,
);
echo _json_encode($response);
