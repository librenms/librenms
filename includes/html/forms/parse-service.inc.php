<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (!Auth::user()->hasGlobalAdmin()) {
    die('ERROR: You need to be admin');
}

$service_id = $vars['service_id'];

if (is_numeric($service_id) && $service_id > 0) {
    $service = service_get(null, $service_id);

    $output = array(
        'stype'     => $service[0]['service_type'],
        'ip'        => $service[0]['service_ip'],
        'desc'      => $service[0]['service_desc'],
        'param'     => $service[0]['service_param'],
        'ignore'    => $service[0]['service_ignore'],
        'disabled'   => $service[0]['service_disabled']
    );

    header('Content-Type: application/json');
    echo _json_encode($output);
}
