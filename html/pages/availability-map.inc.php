<?php
/*
* LibreNMS
*
* Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
* This program is free software: you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation, either version 3 of the License, or (at your
* option) any later version.  Please see LICENSE.txt at the top level of
* the source code distribution for details.
*/

if ($_SESSION['userlevel'] >= '10') {
    $sql = "SELECT `hostname`,`device_id`,`status`,`uptime` FROM `devices` WHERE `ignore` = '0' AND `disabled` = '0' ORDER BY `hostname`";
    $sqlcount = "SELECT COUNT(*) FROM `devices` WHERE `ignore` = '0' AND `disabled` = '0' ORDER BY `hostname`";
    $rows = dbFetchCell($sqlcount);
    echo "<div style='max-width:800px; margin-left:auto; margin-right:auto;'><center><h3>All Devices(" . $rows . ")</h3></center>";
}
else {
    $sql = "SELECT D.`hostname`,D.`device_id`,D.`status`,D.`uptime` FROM `devices` AS D, `devices_perms` AS P WHERE D.`device_id` = P.`device_id` AND P.`user_id` = '" . $_SESSION['user_id'] . "' AND D.`ignore` = '0' AND D.`disabled` = '0' ORDER BY D.`hostname`";
    $sqlcount = "SELECT COUNT(*) FROM `devices` AS D, `devices_perms` AS P WHERE D.`device_id` = P.`device_id` AND P.`user_id` = '" . $_SESSION['user_id'] . "' AND D.`ignore` = '0' AND D.`disabled` = '0' ORDER BY D.`hostname`";
    $rows = dbFetchCell($sqlcount);
    echo "<div style='max-width:800px; margin-left:auto; margin-right:auto;'><center><h3>All Devices(" . $rows . ")</h3></center>";
}

foreach(dbFetchRows($sql) as $device) {
    if ($device['status'] == '1') {
        $btn_type = 'btn-success';
        if ($device['uptime'] < $config['uptime_warning']) {
            $btn_type = 'btn-warning';
        }
    }
    else {
        $btn_type = 'btn-danger';
    }

    echo "<a href='/graphs/type=device_uptime/device=" . $device['device_id'] . "/' role='button' class='btn " . $btn_type . " btn-xs' title='" . $device['hostname'] . "' style='min-height:25px; min-width:25px; border-radius:0px;'></a>";
}

echo "</div>";
