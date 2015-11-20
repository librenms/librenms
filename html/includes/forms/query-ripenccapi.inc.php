<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$status    = 'error';
$message   = 'unknown error';
$parameter = mres($_POST['parameter']);
if (isset($parameter)) {
    $status  = 'ok';
    $message = 'Queried';
    $output = get_ripe_api_whois_data_json($parameter);
}
else {
    $status  = 'error';
    $message = 'ERROR: Could not query';
}
die(json_encode(array(
     'status'       => $status,
     'message'      => $message,
     'parameter'    => $parameter,
     'output'       => $output
)));
