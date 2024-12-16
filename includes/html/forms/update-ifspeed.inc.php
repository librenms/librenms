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

use App\Models\Port;
use LibreNMS\Enum\Severity;

header('Content-type: application/json');

$status = 'error';

$speed = $_POST['speed'];
$port_id = $_POST['port_id'];

$port = Port::with('device')->firstWhere('port_id', $port_id);

if ($port) {
    $port->ifSpeed = $speed;
    if ($port->save()) {
        if (empty($speed)) {
            $port->device->forgetAttrib('ifSpeed:' . $port->ifName);
            \App\Models\Eventlog::log("{$port->ifName} Port speed cleared manually", $port->device, 'interface', Severity::Notice, $port_id);
        } else {
            $port->device->setAttrib('ifSpeed:' . $port->ifName, $speed);
            \App\Models\Eventlog::log("{$port->ifName} Port speed set manually: $speed", $port->device, 'interface', Severity::Notice, $port_id);
            $port_tune = $port->device->getAttrib('ifName_tune:' . $port->ifName);
            $device_tune = $port->device->getAttrib('override_rrdtool_tune');
            if ($port_tune == 'true' ||
                ($device_tune == 'true' && $port_tune != 'false') ||
                (\LibreNMS\Config::get('rrdtool_tune') == 'true' && $port_tune != 'false' && $device_tune != 'false')) {
                $rrdfile = get_port_rrdfile_path($port->device->hostname, $port_id);
                Rrd::tune('port', $rrdfile, $speed);
            }
        }
        $status = 'ok';
    } else {
        $status = 'na';
    }
}

$response = [
    'status' => $status,
];
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
