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
# header('Content-type: text/plain');

$alert_id = $_POST['alert_id'];
if (!is_numeric($alert_id)) {
    echo 'ERROR: No alert selected';
    exit;
} else {
    $proc = dbFetchCell('SELECT proc FROM alerts,alert_rules WHERE alert_rules.id = alerts.rule_id AND alerts.id = ?', array($alert_id));
    if (($proc == "") || ($proc == "NULL")) {
        echo header("HTTP/1.0 404 Not Found");
    } elseif (! preg_match('/^http:\/\//', $proc)) {
        echo "ERROR";
    } else {
        echo $proc;
    }
    exit;
}
