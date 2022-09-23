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

if (! is_numeric($_POST['user_id']) || ! isset($_POST['token'])) {
    echo 'ERROR: error with data, please ensure a valid user and token have been specified.';
    exit;
} elseif (strlen($_POST['token']) > 32) {
    echo 'ERROR: The token is more than 32 characters';
    exit;
} elseif (strlen($_POST['token']) < 16) {
    echo 'ERROR: The token is less than 16 characters';
    exit;
} else {
    $create = dbInsert(['user_id' => $_POST['user_id'], 'token_hash' => $_POST['token'], 'description' => $_POST['description']], 'api_tokens');
    if ($create > '0') {
        echo 'API token has been created';
        Session::put('api_token', true);
        exit;
    } else {
        echo 'ERROR: An error occurred creating the API token';
        exit;
    }
}//end if
