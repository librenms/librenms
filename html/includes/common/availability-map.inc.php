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

if (defined('show_settings')) {

    $common_output[] = '
<form class="form-horizontal" onsubmit="widget_settings(this); return false;">
    <div class="form-group">
        <label for="tile_width" class="col-sm-4 control-label">Tile width</label>
        <div class="col-sm-4">
            <input class="form-control" name="tile_width" id="input_tile_width_'.$unique_id.'" placeholder="I.e 10" value="'.$widget_settings['tile_width'].'">
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-6 col-sm-4">
            <button type="submit" class="btn btn-primary">Set</button>
        </div>
    </div>
</form>
    ';

}
else {

    require_once 'includes/object-cache.inc.php';
    $tile_width = $widget_settings['tile_width']?:$config['availability-map-width'];

    $sql = 'SELECT `D`.`hostname`,`D`.`device_id`,`D`.`status`,`D`.`uptime` FROM `devices` AS `D`';

    if (is_normal_user() === true) {
        $sql.= ' , `devices_perms` AS P WHERE D.`device_id` = P.`device_id` AND P.`user_id` = ? AND';
        $param = array(
            $_SESSION['user_id']
        );
    }
    else {
        $sql.= ' WHERE';
    }

    $sql.= " `D`.`ignore` = '0' AND `D`.`disabled` = '0' ORDER BY `hostname`";
    $temp_output = array();
    $c = '0';

    foreach(dbFetchRows($sql, $param) as $device) {
        if ($device['status'] == '1') {
            $btn_type = 'btn-success';
            if ($device['uptime'] < $config['uptime_warning']) {
                $btn_type = 'btn-warning';
                $c++;
            }
        }
        else {
            $btn_type = 'btn-danger';
        }

        $temp_output[] = '<a href="' . generate_url(array(
            'page' => 'device',
            'device' => $device['device_id']
        )) . '" role="button" class="btn ' . $btn_type . ' btn-xs" title="' . $device['hostname'] . " - " . formatUptime($device['uptime']) . '" style="min-height:' . $tile_width . 'px; min-width: ' . $tile_width . 'px; border-radius:0px; margin:0px; padding:0px;"></a>';
    }

    $temp_rows = count($temp_output);
    $temp_output[] = '</div>';
    $temp_header = array(
        '<div style="margin-left:auto; margin-right:auto;"><center><h5><i class="fa fa-check" style="color:green">' . $devices['up'] . ' </i> <i class="fa fa-exclamation-triangle" style="color:orange"> '. $c .'</i> <i class="fa fa-exclamation-circle" style="color:red"> ' . $devices['down'] . '</i></h5></center><br />'
    );
    $common_output = array_merge($temp_header, $temp_output);
}
