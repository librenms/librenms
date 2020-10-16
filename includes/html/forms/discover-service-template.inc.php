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

$id = $vars['id'];

if (is_numeric($id)) {
    if (discover_service_template($id)) {
        $status = ['status' =>0, 'message' => 'Services for Service Template: <i>' . $id . ', have been created or updated.</i>'];
    } else {
        $status = ['status' =>1, 'message' => 'Services for Service Template: <i>' . $id . ', have NOT been created or updated.</i>'];
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
