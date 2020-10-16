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
    if (remove_service_template($id)) {
        $status = ['status' =>0, 'message' => 'Service Template: <i>' . $id . ', has been removed from Services.</i>'];
    } else {
        $status = ['status' =>1, 'message' => 'Service Template: <i>' . $id . ', has NOT been removed from Services.</i>'];
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
