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

use App\Models\Service;

$service = Service::find(Request::input('service_id'));

if ($service) {
    Gate::authorize('view', $service);
    $output = [
        'stype' => $service->service_type,
        'ip' => $service->service_ip,
        'desc' => $service->service_desc,
        'param' => $service->service_param,
        'ignore' => $service->service_ignore,
        'disabled' => $service->service_disabled,
        'template_id' => $service->service_template_id,
        'name' => $service->service_name,
    ];

    header('Content-Type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
