<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
header('Content-type: application/json');

$status = 'error';
$message = 'unknown error';
$parameters = strip_tags($_POST['search_in_conf_textbox']);
if (isset($parameters)) {
    $message = 'Queried';
    if ($output = search_oxidized_config($parameters)) {
        $status = 'ok';
    }
} else {
    $message = 'ERROR: Could not query';
}
echo \LibreNMS\Util\Clean::html(json_encode([
    'status' => $status,
    'message' => $message,
    'search_in_conf_textbox' => $parameters,
    'output' => $output,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), []);
