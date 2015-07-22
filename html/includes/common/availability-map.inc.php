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

$sql = 'SELECT `D`.`hostname`,`D`.`device_id`,`D`.`status`,`D`.`uptime` FROM `devices` AS `D`';

if (is_admin() === false && is_read() === false) {
    $sql .= ' , `devices_perms` AS P WHERE D.`device_id` = P.`device_id` AND P.`user_id` = ? AND';
    $param = array($_SESSION['user_id']);
}
else {
    $sql .= ' WHERE';
}

$sql .= " `D`.`ignore` = '0' AND `D`.`disabled` = '0' ORDER BY `hostname`";

$temp_output = array();

foreach(dbFetchRows($sql,$param) as $device) {
    if ($device['status'] == '1') {
        $btn_type = 'btn-success';
        if ($device['uptime'] < $config['uptime_warning']) {
            $btn_type = 'btn-warning';
        }
    }
    else {
        $btn_type = 'btn-danger';
    }
    $temp_output[] = '<a href="' .generate_url(array('page' => 'device', 'device' => $device['device_id'])). '" role="button" class="btn ' . $btn_type . ' btn-xs" title="' . $device['hostname'] . '" style="min-height:25px; min-width:25px; border-radius:0px; border:0px; margin:0; padding:0;"></a>';
}

$temp_rows = count($temp_output);

$temp_output[] = '</div>';
$temp_header = array('<div style="margin-left:auto; margin-right:auto;"><center><h3>All Devices(' . $temp_rows . ')</h3></center>');
$common_output = array_merge($temp_header,$temp_output);
