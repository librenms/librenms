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
if (defined('show_settings')) {
    $current_mode = isset($widget_settings['mode']) ? $widget_settings['mode'] : 0;
    $current_width = isset($widget_settings['tile_width']) ? $widget_settings['tile_width'] : 10;

    $common_output[] = '<form class="form-horizontal" onsubmit="return widget_settings(this)">';
    $common_output[] = '<div class="form-group">';
    $common_output[] = '<label for="tile_width" class="col-sm-4 control-label">Tile width</label>';
    $common_output[] = '<div class="col-sm-6">';
    $common_output[] = '<input class="form-control" name="tile_width" placeholder="I.e 10" value="'.$current_width.'">';
    $common_output[] = '</div>';
    $common_output[] = '</div>';
    $common_output[] = '<div class="form-group">';
    $common_output[] = '<label for="show_services" class="col-sm-4 control-label">Show</label>';
    $common_output[] = '<div class="col-sm-6">';
    $common_output[] = '<select class="form-control" name="mode">';
    $common_output[] = '<option value="0" '.($current_mode == 0 ? 'selected':'').'>only devices</option>';
    $common_output[] = '<option value="1"' .($current_mode == 1 ? 'selected':'').'>only services</option>';
    $common_output[] = '<option value="2"' .($current_mode == 2 ? 'selected':'').'>devices and services</option>';
    $common_output[] = '</select>';
    $common_output[] = '</div>';
    $common_output[] = '</div>';
    $common_output[] = '<div class="form-group">';
    $common_output[] = '<div class="col-sm-offset-6 col-sm-4">';
    $common_output[] = '<button type="submit" class="btn btn-primary">Set</button>';
    $common_output[] = '</div>';
    $common_output[] = '</div>';
    $common_output[] = '</form>';

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
                $temp_output[] = '<a href="' . generate_url(array('page' => 'device', 'device' => $device['device_id'])) . '" ';
                $temp_output[] = 'title="' . $device['hostname'] . " - " . formatUptime($device['uptime']) . '">';
                $temp_output[] = '<div class="device-availability ' . $deviceState . '">';
                $temp_output[] = '<label class="availability-label label ' . $deviceLabel . ' label-font-border">' . $deviceState . '</label>';
                $temp_output[] = '<span class="device-icon">' . $deviceIcon . '</span><br>';
                $temp_output[] = '<span class="small">' . $device["hostname"] . '</span>';
                $temp_output[] = '</div></a>';
            } else {
                $temp_output[] = '<a href="' . generate_url(array('page' => 'device', 'device' => $device['device_id'])) . '" ';
                $temp_output[] = 'title="' . $device['hostname'] . " - " . formatUptime($device['uptime']) . '">';
                $temp_output[] = '<label class="label '.$deviceLabel.' widget-availability label-font-border">'.$deviceState.'</label>';
                $temp_output[] = '</a>';
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
                $temp_output[] = '<a href="' . generate_url(array('page' => 'device', 'tab' => 'services', 'device' => $service['device_id'])) . '"';
                $temp_output[] = 'title="' . $service['hostname'] . " - " . $service['service_type'] . " - " . $service['service_desc'] . '">';
                $temp_output[] = '<div class="service-availability ' . $serviceState . '">';
                $temp_output[] = '<label class="service-name-label label ' . $serviceLabel . ' label-font-border">' . $service["service_type"] . '</label>';
                $temp_output[] = '<label class="availability-label label ' . $serviceLabel . ' label-font-border">' . $serviceState . '</label>';
                $temp_output[] = '<span class="device-icon">' . $deviceIcon . '</span><br>';
                $temp_output[] = '<span class="small">' . $service["hostname"] . '</span>';
                $temp_output[] = '</div></a>';
            } else {
                $temp_output[] = '<a href="' . generate_url(array('page' => 'device', 'tab' => 'services', 'device' => $service['device_id'])) . '"';
                $temp_output[] = 'title="' . $service['hostname'] . " - " . $service['service_type'] . " - " . $service['service_desc'] . '">';
                $temp_output[] = '<label class="label '.$serviceLabel.' widget-availability label-font-border">'.$service['service_type'].' - '.$serviceState.'</label>';
                $temp_output[] = '</a>';
            }
        }
    }

    if ($directpage == "yes") {
        $temp_header[] = '<div class="page-availability-title-left">';
        $temp_header[] = '<span class="page-availability-title">Availability map for</span>';
        $temp_header[] = '<select id="mode" class="page-availability-report-select" name="mode">';
        $temp_header[] = '<option value="0" ' . ($mode == 0 ? 'selected' : '') . '>devices</option>';
        $temp_header[] = '<option value="1"' . ($mode == 1 ? 'selected' : '') . '>services</option>';
        $temp_header[] = '<option value="2"' . ($mode == 2 ? 'selected' : '') . '>devices and services</option>';
        $temp_header[] = '</select></div>';
        $temp_header[] = '<div class="page-availability-title-right">';
    }


    if ($mode == 0 || $mode == 2) {
        if ($directpage == "yes") {
            $temp_header[] = '<div class="page-availability-report-host">';
            $temp_header[] = '<span>Hosts report:</span>';
            $temp_header[] = '<div style="float:right;">';
            $temp_header[] = '<div class="small page-availability-report-entry"><label class="label label-success label-font-border">up</label></div>';
            $temp_header[] = '<div class="small page-availability-report-entry"><span>' . $host_up_count . '</span></div>';
            $temp_header[] = '</div>';
            $temp_header[] = '<div style="float:right;">';
            $temp_header[] = '<div class="small page-availability-report-entry"><label class="label label-warning label-font-border">warn</label></div>';
            $temp_header[] = '<div class="small page-availability-report-entry"><span>' . $host_warn_count . '</span></div>';
            $temp_header[] = '</div>';
            $temp_header[] = '<div style="float:right;">';
            $temp_header[] = '<div class="small page-availability-report-entry"><label class="label label-danger label-font-border">down</label></div>';
            $temp_header[] = '<div class="small page-availability-report-entry"><span>' . $host_down_count . '</span></div>';
            $temp_header[] = '</div>';
            $temp_header[] = '</div>';
        } else {
            $temp_header[] = '<div class="widget-availability-host">Total hosts <label class="label label-success label-font-border">up</label> '.$host_up_count.' <label class="label label-warning label-font-border">warning</label> '.$host_warn_count.' <label class="label label-danger label-font-border">down</label> '.$host_down_count.'</div>';
        }
    }

    if ($mode == 1 || $mode == 2) {
        if ($directpage == "yes") {
            $temp_header[] = '<div class="page-availability-report-service">';
            $temp_header[] = '<span>Services report:</span>';
            $temp_header[] = '<div style="float:right;">';
            $temp_header[] = '<div class="small page-availability-report-entry"><label class="label label-success label-font-border">up</label></div>';
            $temp_header[] = '<div class="small page-availability-report-entry"><span>' . $service_up_count . '</span></div>';
            $temp_header[] = '</div>';
            $temp_header[] = '<div style="float:right;">';
            $temp_header[] = '<div class="small page-availability-report-entry"><label class="label label-warning label-font-border">warn</label></div>';
            $temp_header[] = '<div class="small page-availability-report-entry"><span>' . $service_warn_count . '</span></div>';
            $temp_header[] = '</div>';
            $temp_header[] = '<div style="float:right;">';
            $temp_header[] = '<div class="small page-availability-report-entry"><label class="label label-danger label-font-border">down</label></div>';
            $temp_header[] = '<div class="small page-availability-report-entry"><span>' . $service_down_count . '</span></div>';
            $temp_header[] = '</div>';
            $temp_header[] = '</div>';
        } else {
            $temp_header[] = '<div class="widget-availability-service">Total services <label class="label label-success label-font-border">up</label> '.$service_up_count.' <label class="label label-warning label-font-border">warn</label> '.$service_warn_count.' <label class="label label-danger label-font-border">down</label> '.$service_down_count.'</div>';
        }
    }

    $temp_header[] = '</div>';
    $temp_header[] = '<br style="clear:both;">';

    $common_output = array_merge($temp_header, $temp_output);
}