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

$service_id = $vars['service_id'];

if (! is_numeric($service_id)) {
    echo 'ERROR: No service selected';
    exit;
} else {
    if ($_POST['state'] == 'true') {
        $state = 0;
    } elseif ($_POST['state'] == 'false') {
        $state = 1;
    } else {
        $state = 1;
    }

    $update = ['service_disabled' => $state];
    if (is_numeric(edit_service($update, $service_id))) {
        echo 'Service has been updated.';
        exit;
    } else {
        echo 'ERROR: Service has not been updated.';
        exit;
    }
}
