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

$alert_id = mres($_POST['alert_id']);
if(!is_numeric($alert_id)) {
    echo('ERROR: No alert selected');
    exit;
} else {
    if(dbUpdate(array('state' => '2'), 'alerts', 'id=?',array($alert_id))) {
      echo('Alert has been acknowledged.');
      exit;
    } else {
      echo('ERROR: Alert has not been acknowledged.');
      exit;
    }
}

