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

echo '<td>';
if (device_permitted($device['device_id'])) {
    echo '<div class="row">
        <div class="col-xs-1">';
    echo '<a href="'.generate_device_url($device).'"> <img src="images/16/server.png" border="0" align="absmiddle" alt="View device" title="View device" /></a> ';
    echo '</div>
        <div class="col-xs-1">';
    echo '<a href="'.generate_device_url($device, array('tab' => 'alerts')).'"> <img src="images/16/bell.png" border="0" align="absmiddle" alt="View alerts" title="View alerts"  /></a> ';
    echo '</div>';
    if ($_SESSION['userlevel'] >= '7') {
        echo '<div class="col-xs-1">
            <a href="'.generate_device_url($device, array('tab' => 'edit')).'"> <img src="images/16/wrench.png" border="0" align="absmiddle" alt="Edit device" title="Edit device" /></a>
            </div>';
    }

    echo '</div>
        <div class="row">
        <div class="col-xs-1">
        <a href="telnet://'.$device['hostname'].'"><img src="images/16/telnet.png" alt="telnet" title="Telnet to '.$device['hostname'].'" border="0" width="16" height="16"></a>
        </div>
        <div class="col-xs-1">
        <a href="ssh://'.$device['hostname'].'"><img src="images/16/ssh.png" alt="ssh" title="SSH to '.$device['hostname'].'" border="0" width="16" height="16"></a>
        </div>
        <div class="col-xs-1">
        <a href="https://'.$device['hostname'].'"><img src="images/16/http.png" alt="https" title="Launch browser https://'.$device['hostname'].'" border="0" width="16" height="16" target="_blank" rel="noopener"></a>
        </div>
        </div>';
}//end if

echo '</td>';
