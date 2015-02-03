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

echo('<td>');
    if (device_permitted($device['device_id'])) {
        echo '<a href="'.generate_device_url($device).'"> <img src="images/16/server.png" border="0" align="absmiddle" alt="View device" /></a> ';
        echo '<a href="'.generate_device_url($device, array('tab' => 'alerts')).'"> <img src="images/16/bell.png" border="0" align="absmiddle" alt="View alerts"  /></a> ';
        if ($_SESSION['userlevel'] >= "7") {
            echo '<a href="'.generate_device_url($device, array('tab' => 'edit')).'"> <img src="images/16/wrench.png" border="0" align="absmiddle" alt="Edit device" /></a> ';
        }
    }
echo('</td>');

?>
