<?php
/* Copyright (C) 2015 Sergiusz Paprzycki <serek@walcz.net>
 * Copyright (C) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 *
 * This widget is based on legacy frontpage module created by Paul Gear.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$top_query = $widget_settings['top_query'];
$sort_order = $widget_settings['sort_order'];

$selected_sort_asc = '';
$selected_sort_desc = '';

if ($sort_order === 'asc') {
    $selected_sort_asc = 'selected';
} elseif ($sort_order === 'desc') {
    $selected_sort_desc = 'selected';
}

$selected_traffic = '';
$selected_uptime = '';
$selected_ping = '';
$selected_cpu = '';
$selected_ram = '';
$selected_poller = '';

switch ($top_query) {
    case "traffic":
        $table_header = 'Traffic';
        $selected_traffic = 'selected';
        $graph_type = 'device_bits';
        $graph_params = array();
        break;
    case "uptime":
        $table_header = 'Uptime';
        $selected_uptime = 'selected';
        $graph_type = 'device_uptime';
        $graph_params = array('tab' => 'graphs', 'group' => 'system');
        break;
    case "ping":
        $table_header = 'Response time';
        $selected_ping = 'selected';
        $graph_type = 'device_ping_perf';
        $graph_params = array('tab' => 'graphs', 'group' => 'poller');
        break;
    case "cpu":
        $table_header = 'CPU Load';
        $selected_cpu = 'selected';
        $graph_type = 'device_processor';
        $graph_params = array('tab' => 'health', 'metric' => 'processor');
        break;
    case "ram":
        $table_header = 'Memory usage';
        $selected_ram = 'selected';
        $graph_type = 'device_mempool';
        $graph_params = array('tab' => 'health', 'metric' => 'mempool');
        break;
    case "poller":
        $table_header = 'Poller duration';
        $selected_poller = 'selected';
        $graph_type = 'device_poller_perf';
        $graph_params = array('tab' => 'graphs', 'group' => 'poller');
}

$widget_settings['device_count']  = $widget_settings['device_count'] > 0 ? $widget_settings['device_count'] : 5;
$widget_settings['time_interval'] = $widget_settings['time_interval'] > 0 ? $widget_settings['time_interval'] : 15;

if (defined('SHOW_SETTINGS') || empty($widget_settings)) {
    $common_output[] = '
    <form class="form" onsubmit="widget_settings(this); return false;">
        <div class="form-group">
            <div class="col-sm-4">
                <label for="title" class="control-label availability-map-widget-header">Widget title</label>
            </div>
            <div class="col-sm-6">
                <input type="text" class="form-control" name="title" placeholder="Custom title for widget" value="' . htmlspecialchars($widget_settings['title']) . '">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4">
                <label for="top_query" class="control-label availability-map-widget-header">Top query</label>
            </div>
            <div class="col-sm-6">
                <select class="form-control" name="top_query">
                    <option value="traffic" ' . $selected_traffic . '>Traffic</option>
                    <option value="uptime" ' . $selected_uptime . '>Uptime</option>
                    <option value="ping" ' . $selected_ping . '>Response time</option>
                    <option value="poller" ' . $selected_poller . '>Poller duration</option>
                    <option value="cpu" ' . $selected_cpu . '>Processor load</option>
                    <option value="ram" ' . $selected_ram . '>Memory usage</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4">
                <label for="top_query" class="control-label availability-map-widget-header">Sort order</label>
            </div>
            <div class="col-sm-6">
                <select class="form-control" name="sort_order">
                    <option value="asc" ' . $selected_sort_asc . '>Ascending</option>
                    <option value="desc" ' . $selected_sort_desc . '>Descending</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4">
                <label for="graph_type" class="control-label availability-map-widget-header">Number of Devices</label>
            </div>
            <div class="col-sm-6">
                <input class="form-control" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode >= 48 && event.charCode <= 57" name="device_count" id="input_count_' . $unique_id . '" value="' . $widget_settings['device_count'] . '">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4">
                <label for="graph_type" class="control-label availability-map-widget-header">Time interval (minutes)</label>
            </div>
            <div class="col-sm-6">
                <input class="form-control" onkeypress="return (event.charCode == 8 || event.charCode == 0) ? null : event.charCode >= 48 && event.charCode <= 57" name="time_interval" id="input_time_' . $unique_id . '" value="' . $widget_settings['time_interval'] . '">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-10">
                <button type="submit" class="btn btn-default">Set</button>
            </div>
        </div>
    </form>';
} else {
    $interval = $widget_settings['time_interval'];
    (integer)$interval_seconds = ($interval * 60);
    (integer)$device_count = $widget_settings['device_count'];

    $common_output[] = '<h4>Top ' . $device_count . ' devices (last ' . $interval . ' minutes)</h4>';

    $params = array('user' => $_SESSION['user_id'], 'interval' => array($interval_seconds), 'count' => array($device_count));

    if ($top_query === 'traffic') {
        if (is_admin() || is_read()) {
            $query = '
            SELECT *, sum(p.ifInOctets_rate + p.ifOutOctets_rate) as total
            FROM ports as p, devices as d
            WHERE d.device_id = p.device_id
            AND unix_timestamp() - p.poll_time < :interval 
            AND ( p.ifInOctets_rate > 0
            OR p.ifOutOctets_rate > 0 )
            GROUP BY d.device_id
            ORDER BY total ' . $sort_order . '
            LIMIT :count
            ';
        } else {
            $query = '
            SELECT *, sum(p.ifInOctets_rate + p.ifOutOctets_rate) as total
            FROM ports as p, devices as d, `devices_perms` AS `P`
            WHERE `P`.`user_id` = :user AND `P`.`device_id` = `d`.`device_id` AND
            d.device_id = p.device_id
            AND unix_timestamp() - p.poll_time < :interval 
            AND ( p.ifInOctets_rate > 0
            OR p.ifOutOctets_rate > 0 )
            GROUP BY d.device_id
            ORDER BY total ' . $sort_order . '
            LIMIT :count
            ';
        }
    } elseif ($top_query === 'uptime') {
        if (is_admin() || is_read()) {
            $query = 'SELECT `uptime`, `hostname`, `last_polled`, `device_id` 
                      FROM `devices` 
                      WHERE unix_timestamp() - UNIX_TIMESTAMP(`last_polled`) < :interval 
                      ORDER BY `uptime` ' . $sort_order . ' 
                      LIMIT :count';
        } else {
            $query = 'SELECT `uptime`, `hostname`, `last_polled`, `d`.`device_id` 
                      FROM `devices` as `d`, `devices_perms` AS `P`
                      WHERE  `P`.`user_id` = :user
                      AND `P`.`device_id` = `d`.`device_id`
                      AND unix_timestamp() - UNIX_TIMESTAMP(`last_polled`) < :interval
                      ORDER BY `uptime` ' . $sort_order . ' 
                      LIMIT :count';
        }
    } elseif ($top_query === 'ping') {
        if (is_admin() || is_read()) {
            $query = 'SELECT `last_ping_timetaken`, `hostname`, `last_polled`, `device_id` 
                      FROM `devices` 
                      WHERE unix_timestamp() - UNIX_TIMESTAMP(`last_polled`) < :interval 
                      ORDER BY `last_ping_timetaken` ' . $sort_order . ' 
                      LIMIT :count';
        } else {
            $query = 'SELECT `last_ping_timetaken`, `hostname`, `last_polled`, `d`.`device_id` 
                      FROM `devices` as `d`, `devices_perms` AS `P` 
                      WHERE `P`.`user_id` = :user 
                      AND `P`.`device_id` = `d`.`device_id` 
                      AND unix_timestamp() - UNIX_TIMESTAMP(`last_polled`) < :interval 
                      ORDER BY `last_ping_timetaken` ' . $sort_order . ' 
                      LIMIT :count';
        }
    } elseif ($top_query === 'cpu') {
        if (is_admin() || is_read()) {
            $query = 'SELECT `hostname`, `last_polled`, `d`.`device_id`, avg(`processor_usage`) as `cpuload` 
                      FROM `processors` AS `procs`, `devices` AS `d` 
                      WHERE `d`.`device_id` = `procs`.`device_id` 
                      AND unix_timestamp() - UNIX_TIMESTAMP(`last_polled`) < :interval 
                      GROUP BY `d`.`device_id` 
                      ORDER BY `cpuload` ' . $sort_order . ' 
                      LIMIT :count';
        } else {
            $query = 'SELECT `hostname`, `last_polled`, `d`.`device_id`, avg(`processor_usage`) as `cpuload` 
                      FROM `processors` AS procs, `devices` AS `d`, `devices_perms` AS `P`
					  WHERE `P`.`user_id` = :user AND `P`.`device_id` = `procs`.`device_id` 
                      AND unix_timestamp() - UNIX_TIMESTAMP(`last_polled`) < :interval
                      GROUP BY `procs`.`device_id` 
                      ORDER BY `cpuload` ' . $sort_order . '
                      LIMIT :count';
        }
    } elseif ($top_query === 'ram') {
        if (is_admin() || is_read()) {
            $query = 'SELECT `hostname`, `last_polled`, `d`.`device_id`, `mempool_perc` 
                      FROM `mempools` as `mem`, `devices` as `d`
                      WHERE `d`.`device_id` = `mem`.`device_id`
                      AND `mempool_descr` IN (\'Physical memory\',\'Memory\')
                      AND unix_timestamp() - UNIX_TIMESTAMP(`last_polled`) < :interval 
                      ORDER BY `mempool_perc` ' . $sort_order . '
                      LIMIT :count';
        } else {
            $query = 'SELECT `hostname`, `last_polled`, `d`.`device_id`, `mempool_perc` 
                      FROM `mempools` as `mem`, `devices` as `d`, `devices_perms` AS `P`
                      WHERE `P`.`user_id` = :user AND `P`.`device_id` = `mem`.`device_id`
                      AND `mempool_descr` IN (\'Physical memory\',\'Memory\')
                      AND unix_timestamp() - UNIX_TIMESTAMP(`last_polled`) < :interval 
                      ORDER BY `mempool_perc` ' . $sort_order . '
                      LIMIT :count';
        }
    } elseif ($top_query === 'poller') {
        if (is_admin() || is_read()) {
            $query = 'SELECT `last_polled_timetaken`, `hostname`, `last_polled`, `device_id` 
                      FROM `devices` 
                      WHERE unix_timestamp() - UNIX_TIMESTAMP(`last_polled`) < :interval 
                      ORDER BY `last_polled_timetaken` ' . $sort_order . ' 
                      LIMIT :count';
        } else {
            $query = 'SELECT `last_polled_timetaken`, `hostname`, `last_polled`, `d`.`device_id` 
                      FROM `devices` as `d`, `devices_perms` AS `P` 
                      WHERE `P`.`user_id` = :user 
                      AND `P`.`device_id` = `d`.`device_id` 
                      AND unix_timestamp() - UNIX_TIMESTAMP(`last_polled`) < :interval 
                      ORDER BY `last_polled_timetaken` ' . $sort_order . ' 
                      LIMIT :count';
        }
    }


    $common_output[] = '
    <div class="table-responsive">
        <table class="table table-hover table-condensed table-striped bootgrid-table">
        <thead>
            <tr>
                <th class="text-left">Device</th>
                <th class="text-left">' . $table_header . '</a></th>
            </tr>
        </thead>
        <tbody>';

    foreach (dbFetchRows($query, $params) as $result) {
        $common_output[] = '
        <tr>
            <td class="text-left">' . generate_device_link($result, shorthost($result['hostname'])) . '</td>
            <td class="text-left">' . generate_device_link($result, generate_minigraph_image($result, $config['time']['day'], $config['time']['now'], $graph_type, 'no', 150, 21), $graph_params, 0, 0, 0) . '</td>
        </tr>';
    }
    $common_output[] = '
        </tbody>
    </table>
    </div>';
}
