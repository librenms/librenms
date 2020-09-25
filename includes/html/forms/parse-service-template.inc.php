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

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

$service_template_id = $vars['service_template_id'];

if (is_numeric($service_template_id) && $service_template_id > 0) {
    $service_template = service_template_get($service_template_id);

    $output = [
        'device_group_id' => $service_template[0]['device_group_id'],
        'stype'     => $service_template[0]['service_template_type'],
        'desc'      => $service_template[0]['service_template_desc'],
        'ip'        => $service_template[0]['service_template_ip'],
        'param'     => $service_template[0]['service_template_param'],
        'ignore'    => $service_template[0]['service_template_ignore'],
        'disabled'  => $service_template[0]['service_template_disabled'],
        'name'      => $service_template[0]['service_template_name'],
    ];

    header('Content-Type: application/json');
    echo _json_encode($output);
}
