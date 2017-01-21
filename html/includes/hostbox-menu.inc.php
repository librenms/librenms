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
    echo '<a href="'.generate_device_url($device).'">  <i class="fa fa-server fa-lg icon-theme" title="View device" aria-hidden="true"></i></a> ';
    echo '</div>
        <div class="col-xs-1">';
    echo '<a href="'.generate_device_url($device, array('tab' => 'alerts')).'"> <i class="fa fa-exclamation-circle fa-lg icon-theme"  title="View alerts" aria-hidden="true"></i></a> ';
    echo '</div>';
    if ($_SESSION['userlevel'] >= '7') {
        echo '<div class="col-xs-1">
            <a href="'.generate_device_url($device, array('tab' => 'edit')).'"> <i class="fa fa-pencil fa-lg icon-theme"  title="Edit ports" aria-hidden="true"></i></a>
            </div>';
    }

    echo '</div>
        <div class="row">
        <div class="col-xs-1">
        <a href="telnet://'.$device['hostname'].'"><i class="fa fa-terminal fa-lg icon-theme"  title="Telnet to ' . $device['hostname'] . '"></a>
        </div>
        <div class="col-xs-1">
        <a href="ssh://'.$device['hostname'].'"><i class="fa fa-lock fa-lg icon-theme"  title="SSH to ' . $device['hostname'] . '"></a>
        </div>
        <div class="col-xs-1">
        <a href="https://' . $device['hostname'] . '" target="_blank" rel="noopener"><i class="fa fa-globe fa-lg icon-theme"  title="Launch browser https://' . $device['hostname'] . '"></i></a>
        </div>
        </div>';
}//end if

echo '</td>';
