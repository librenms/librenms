<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * Copyright (c) 2016 Jens Langhammer <jens@beryju.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (defined('show_settings')) {
    $current_mode = isset($widget_settings['mode']) ? $widget_settings['mode'] : 0;
    $current_width = isset($widget_settings['tile_width']) ? $widget_settings['tile_width'] : 10;
    $common_output[] = '
    <form class="form-horizontal" onsubmit="widget_settings(this); return false;">
        <div class="form-group">
            <label for="tile_width" class="col-sm-4 control-label">Tile width</label>
            <div class="col-sm-4">
                <input class="form-control" name="tile_width" placeholder="I.e 10" value="'.$current_width.'">
            </div>
        </div>
        <div class="form-group">
            <label for="show_services" class="col-sm-4 control-label">Show</label>
            <div class="col-sm-4">
                <select class="form-control" name="mode">
                    <option value="0" '.($current_mode == 0 ? 'selected':'').'>Only Devices</option>
                    <option value="1"' .($current_mode == 1 ? 'selected':'').'>Only Services</option>
                    <option value="2"' .($current_mode == 2 ? 'selected':'').'>Devices and Services</option>
                </select>
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
    $mode = isset($widget_settings['mode']) ? $widget_settings['mode'] : 0;
    $tile_width = isset($widget_settings['tile_width']) ? $widget_settings['tile_width'] : 10;

    $up_count = 0;
    $warn_count = 0;
    $down_count = 0;

    if ($mode == 0 || $mode == 2) {
        // Only show devices if mode is 0 or 2 (Only Devices or both)
        $sql = 'SELECT `D`.`hostname`,`D`.`device_id`,`D`.`status`,`D`.`uptime` FROM `devices` AS `D`';
        if (is_normal_user() === true) {
            $sql .= ' , `devices_perms` AS P WHERE D.`device_id` = P.`device_id` AND P.`user_id` = ? AND';
            $param = array(
                $_SESSION['user_id']
                );
        }
        else {
            $sql .= ' WHERE';
        }
        $sql .= " `D`.`ignore` = '0' AND `D`.`disabled` = '0' ORDER BY `hostname`";
        $temp_output = array();

        foreach (dbFetchRows($sql, $param) as $device) {
            if ($device['status'] == '1') {
                if (($device['uptime'] < $config['uptime_warning']) && ($device['uptime'] != '0')) {
                    $btn_type = 'btn-warning';
                    $warn_count++;
                }
                else {
                    $btn_type = 'btn-success';
                    $up_count ++;
                }
            }
            else {
                $btn_type = 'btn-danger';
                $down_count++;
            }

            $temp_output[] = '<a href="' . generate_url(array(
                'page' => 'device',
                'device' => $device['device_id']
                )) . '" role="button" class="btn ' . $btn_type . ' btn-xs" title="' . $device['hostname'] . " - " . formatUptime($device['uptime']) . '" style="min-height:' . $tile_width . 'px; min-width: ' . $tile_width . 'px; border-radius:0px; margin:0px; padding:0px;"></a>';
        }
    }

    if ($mode == 1 || $mode == 2) {
        $service_query = 'select `S`.`service_type`, `S`.`service_id`, `S`.`service_desc`, `S`.`service_status`, `D`.`hostname`, `D`.`device_id` from services S, devices D where `S`.`device_id` = `D`.`device_id`;';
        foreach (dbFetchRows($service_query) as $service) {
            if ($service['service_status'] == '0') {
                $btn_type = 'btn-success';
                $up_count ++;
            }
            else {
                $btn_type = 'btn-danger';
                $down_count += 1;
            }

            $temp_output[] = '<a href="' . generate_url(array(
                'page' => 'device',
                'tab' => 'services',
                'device' => $service['device_id']
                )) . '" role="button" class="btn ' . $btn_type . ' btn-xs" title="' .$service['hostname']." - ".$service['service_type']." - ".$service['service_desc'] . '" style="min-height:' . $tile_width . 'px; min-width: ' . $tile_width . 'px; border-radius:0px; margin:0px; padding:0px;"></a>';
        }
    }

    $temp_output[] = '</div>';
    $temp_header   = array(
        '<div style="margin-left:auto; margin-right:auto;"><center><h5><i class="fa fa-check" style="color:green">'.$up_count.' </i> <i class="fa fa-exclamation-triangle" style="color:orange"> '.$warn_count.'</i> <i class="fa fa-exclamation-circle" style="color:red"> '.$down_count.'</i></h5></center><br />'
        );
    $common_output = array_merge($temp_header, $temp_output);
}
