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

if (! is_numeric($_POST['alert_id'])) {
    echo 'ERROR: No alert selected';
    exit;
} else {
    if ($_POST['state'] == 'true') {
        $state = 0;
    } elseif ($_POST['state'] == 'false') {
        $state = 1;
    } else {
        $state = 1;
    }

    $update = dbUpdate(['disabled' => $state], 'alert_rules', '`id`=?', [$_POST['alert_id']]);
    if (! empty($update) || $update == '0') {
        echo 'Alert rule has been updated.';
        exit;
    } else {
        echo 'ERROR: Alert rule has not been updated.';
        exit;
    }
}
