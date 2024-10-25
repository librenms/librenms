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

header('Content-type: text/plain');

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

$token = bin2hex(openssl_random_pseudo_bytes(16));

if (! is_numeric($_POST['user_id'])) {
    echo 'ERROR: error with data, please ensure a valid user and token have been specified.';
    exit;
} else {
    $create = dbInsert(['user_id' => $_POST['user_id'], 'token_hash' => $token, 'description' => $_POST['description']], 'api_tokens');
    if ($create > '0') {
        echo 'API token has been created';
        Session::put('api_token', true);
        exit;
    } else {
        echo 'ERROR: An error occurred creating the API token';
        exit;
    }
}//end if
