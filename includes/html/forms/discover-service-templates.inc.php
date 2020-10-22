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

if (discover_service_templates()) {
    $status = ['status' =>0, 'message' => 'Services created or updated.'];
} else {
    $status = ['status' =>1, 'message' => 'No Services created or updated.'];
}
header('Content-Type: application/json');
echo _json_encode($status);
