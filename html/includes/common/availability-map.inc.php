<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * Copyright (c) 2016 Jens Langhammer <jens@beryju.org>
 * Copyright (c) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
if (defined('SHOW_SETTINGS')) {
    $current_mode = isset($widget_settings['mode']) ? $widget_settings['mode'] : 0;
    $current_width = isset($widget_settings['tile_width']) ? $widget_settings['tile_width'] : 10;

    $common_output[] = '
    <form class="form-horizontal" onsubmit="return widget_settings(this)">
    <div class="form-group">
        <label for="tile_width" class="col-sm-4 control-label">Tile width</label>
        <div class="col-sm-6">
            <input class="form-control" name="tile_width" placeholder="I.e 10" value="'.$current_width.'">
        </div>
    </div>
    <div class="form-group">
        <label for="show_services" class="col-sm-4 control-label">Show</label>
        <div class="col-sm-6">
            <select class="form-control" name="mode">
                <option value="0" '.($current_mode == 0 ? 'selected':'').'>only devices</option>
                <option value="1"' .($current_mode == 1 ? 'selected':'').'>only services</option>
                <option value="2"' .($current_mode == 2 ? 'selected':'').'>devices and services</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-6 col-sm-4"><button type="submit" class="btn btn-primary">Set</button></div>
    </div>
    </form>';
} else {
    require_once 'includes/object-cache.inc.php';
    $mode = isset($_SESSION["mapView"]) ? $_SESSION["mapView"] : 0;

    $widget_mode = isset($widget_settings['mode']) ? $widget_settings['mode'] : 0;
    $tile_width = isset($widget_settings['tile_width']) ? $widget_settings['tile_width'] : 10;

    $host_up_count = 0;
    $host_warn_count = 0;
    $host_down_count = 0;
    $service_up_count = 0;
    $service_warn_count = 0;
    $service_down_count = 0;

    if ($mode == 0 || $mode == 2) {
        // Only show devices if mode is 0 or 2 (Only Devices or both)
        $sql = 'SELECT `D`.`hostname`,`D`.`device_id`,`D`.`status`,`D`.`uptime`, `D`.`os`, `D`.`icon` FROM `devices` AS `D`';
        if (is_normal_user() === true) {
            $sql .= ' , `devices_perms` AS P WHERE D.`device_id` = P.`device_id` AND P.`user_id` = ? AND';
            $param = array(
                $_SESSION['user_id']
            );
        } else {
            $sql .= ' WHERE';
        }
        $sql .= " `D`.`ignore` = '0' AND `D`.`disabled` = '0' ORDER BY `hostname`";
        $temp_output = array();

        foreach (dbFetchRows($sql, $param) as $device) {
            if ($device['status'] == '1') {
                if (($device['uptime'] < $config['uptime_warning']) && ($device['uptime'] != '0')) {
                    $deviceState = 'warn';
                    $deviceLabel = 'label-warning';
                    $host_warn_count++;
                } else {
                    $deviceState = 'up';
                    $deviceLabel = 'label-success';
                    $host_up_count++;
                }
            } else {
                $deviceState = 'down';
                $deviceLabel = 'label-danger';
                $host_down_count++;
            }

            if ($directpage == "yes") {
                $deviceIcon = getImage($device);
                $temp_output[] = '
                <a href="'.generate_url(array('page' => 'device', 'device' => $device['device_id'])).'" title="'.$device['hostname']." - ".formatUptime($device['uptime']).'">
                    <div class="device-availability '.$deviceState.'">
                        <span class="availability-label label '.$deviceLabel.' label-font-border">'.$deviceState.'</span>
                        <span class="device-icon">'.$deviceIcon.'</span><br>
                        <span class="small">'.$device["hostname"].'</span>
                    </div>
                </a>';
            } else {
                $temp_output[] = '
                <a href="'.generate_url(array('page' => 'device', 'device' => $device['device_id'])).'" title="'.$device['hostname']." - ".formatUptime($device['uptime']).'">
                    <span class="label '.$deviceLabel.' widget-availability label-font-border">'.$deviceState.'</span>
                </a>';
            }
        }
    }

    if ($mode == 1 || $mode == 2) {
        $service_query = 'select `S`.`service_type`, `S`.`service_id`, `S`.`service_desc`, `S`.`service_status`, `D`.`hostname`, `D`.`device_id`, `D`.`os`, `D`.`icon` from services S, devices D where `S`.`device_id` = `D`.`device_id`;';
        foreach (dbFetchRows($service_query) as $service) {
            if ($service['service_status'] == '0') {
                $serviceLabel = "label-success";
                $serviceState = "up";
                $service_up_count++;
            } elseif ($service['service_status'] == '1') {
                $serviceLabel = "label-warning";
                $serviceState = "warn";
                $service_warn_count++;
            } else {
                $serviceLabel = "label-danger";
                $serviceState = "down";
                $service_down_count++;
            }

            if ($directpage == "yes") {
                $deviceIcon = getImage($service);
                $temp_output[] = '
                <a href="'.generate_url(array('page' => 'device', 'tab' => 'services', 'device' => $service['device_id'])).'" title="'.$service['hostname']." - ".$service['service_type']." - ".$service['service_desc'].'">
                    <div class="service-availability '.$serviceState.'">
                        <span class="service-name-label label '.$serviceLabel.' label-font-border">'.$service["service_type"].'</span>
                        <span class="availability-label label '.$serviceLabel.' label-font-border">'.$serviceState.'</span>
                        <span class="device-icon">'.$deviceIcon.'</span><br>
                        <span class="small">'.$service["hostname"].'</span>
                    </div>
                </a>';
            } else {
                $temp_output[] = '
                <a href="'.generate_url(array('page' => 'device', 'tab' => 'services', 'device' => $service['device_id'])).'" title="'.$service['hostname']." - ".$service['service_type']." - ".$service['service_desc'].'">
                    <span class="label '.$serviceLabel.' widget-availability label-font-border">'.$service['service_type'].' - '.$serviceState.'</span>
                </a>';
            }
        }
    }

    if ($directpage == "yes") {
        $temp_header[] = '
        <div class="page-availability-title-left">
            <span class="page-availability-title">Availability map for</span>
            <select id="mode" class="page-availability-report-select" name="mode">
                <option value="0"'.($mode == 0 ? 'selected' : '').'>devices</option>
                <option value="1"'.($mode == 1 ? 'selected' : '').'>services</option>
                <option value="2"'.($mode == 2 ? 'selected' : '').'>devices and services</option>
            </select>
        </div>
        <div class="page-availability-title-right">';
    }

    if ($mode == 0 || $mode == 2) {
        if ($directpage == "yes") {
            $headerClass = 'page-availability-report-host';
        } else {
            $headerClass = 'widget-availability-host';
        }
            $temp_header[] = '
            <div class="'.$headerClass.'">
                <span>Total hosts</span>
                <span class="label label-success label-font-border label-border">up: '.$host_up_count.'</span>
                <span class="label label-warning label-font-border label-border">warn: '.$host_warn_count.'</span>
                <span class="label label-danger label-font-border label-border">down: '.$host_down_count.'</span>
            </div>';
    }

    if ($mode == 1 || $mode == 2) {
        if ($directpage == "yes") {
            $headerClass = 'page-availability-report-service';
        } else {
            $headerClass = 'widget-availability-service';
        }
            $temp_header[] = '
            <div class="'.$headerClass.'">
                <span>Total services</span>
                <span class="label label-success label-font-border label-border">up: '.$service_up_count.'</span>
                <span class="label label-warning label-font-border label-border">warn: '.$service_warn_count.'</span>
                <span class="label label-danger label-font-border label-border">down: '.$service_down_count.'</span>
            </div>';
    }

    $temp_header[] = '</div>';
    $temp_header[] = '<br style="clear:both;">';

    $common_output = array_merge($temp_header, $temp_output);
}
