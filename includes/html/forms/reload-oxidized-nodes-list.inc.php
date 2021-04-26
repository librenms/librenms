<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (! Auth::user()->hasGlobalAdmin()) {
    $status = 'error';
    $message = 'ERROR: You need to be admin to reload Oxidized node list';
} else {
    oxidized_reload_nodes();
    $status = 'ok';
    $message = 'Oxidized node list was reloaded';
}
$output = [
    'status'  => $status,
    'message' => $message,
];
header('Content-type: application/json');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
