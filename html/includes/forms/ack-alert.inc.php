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

$alert_id = mres($_POST['alert_id']);
$state    = mres($_POST['state']);
if (!is_numeric($alert_id)) {
    echo 'ERROR: No alert selected';
    exit;
}
else if (!is_numeric($state)) {
    echo 'ERROR: No state passed';
    exit;
}
else {
    if ($state == 2) {
        $state = dbFetchCell('SELECT alerted FROM alerts WHERE id = ?', array($alert_id));
    }
    else if ($state >= 1) {
        $state = 2;
    }

    if (dbUpdate(array('state' => $state), 'alerts', 'id=?', array($alert_id)) >= 0) {
        echo 'Alert acknowledged status changed.';
        exit;
    }
    else {
        echo 'ERROR: Alert has not been acknowledged.';
        exit;
    }
}//end if
