<?php
/* Copyright (C) 2015 Sergiusz Paprzycki <serek@walcz.net>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Top devices by traffic
 * @author Sergiusz Paprzycki
 * @copyright 2015 Sergiusz Paprzycki <serek@walcz.net>
 * @license GPL
 * @package LibreNMS
 * @subpackage Widgets
 */

if( defined('show_settings') || empty($widget_settings) ) {
    $common_output[] = '
<form class="form" onsubmit="widget_settings(this); return false;">
  <div class="form-group">
    <div class="col-sm-6">
      <label for="graph_type" class="control-label">Number of Devices: </label>
    </div>
    <div class="col-sm-4">
      <input class="form-control" name="device_count" id="input_count_'.$unique_id.'" placeholder="ie. 5" value="'.$widget_settings['device_count'].'">
    </div>
  </div>
  <div class="clearfix"></div>
  <div class="form-group">
    <div class="col-sm-6">
      <label for="graph_type" class="control-label">Time interval (minutes): </label>
    </div>
    <div class="col-sm-4">
      <input class="form-control" name="time_interval" id="input_time_'.$unique_id.'" placeholder="ie. 15" value="'.$widget_settings['time_interval'].'">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-2">
      <button type="submit" class="btn btn-default">Set</button>
    </div>
  </div>
</form>
    ';
}
else {
    $interval = $widget_settings['time_interval'];
    (integer) $interval_seconds = ($interval * 60);
    (integer) $device_count = $widget_settings['device_count'];
    $common_output[] = '
<h4>Top '.$device_count.' devices (last '.$interval.' minutes)</h4>
    ';
    $params = array('user' => $_SESSION['user_id'], 'interval' => array($interval_seconds), 'count' => array($device_count));
    if (is_admin() || is_read()) {
        $query = '
            SELECT *, sum(p.ifInOctets_rate + p.ifOutOctets_rate) as total
            FROM ports as p, devices as d
            WHERE d.device_id = p.device_id
            AND unix_timestamp() - p.poll_time < :interval 
            AND ( p.ifInOctets_rate > 0
            OR p.ifOutOctets_rate > 0 )
            GROUP BY d.device_id
            ORDER BY total desc
            LIMIT :count
            ';
    }
    else {
        $query = '
            SELECT *, sum(p.ifInOctets_rate + p.ifOutOctets_rate) as total
            FROM ports as p, devices as d, `devices_perms` AS `P`
            WHERE `P`.`user_id` = :user AND `P`.`device_id` = `d`.`device_id` AND
            d.device_id = p.device_id
            AND unix_timestamp() - p.poll_time < :interval 
            AND ( p.ifInOctets_rate > 0
            OR p.ifOutOctets_rate > 0 )
            GROUP BY d.device_id
            ORDER BY total desc
            LIMIT :count
            ';
    }
    $common_output[] = '
<div class="table-responsive">
<table class="table table-hover table-condensed table-striped bootgrid-table">
  <thead>
    <tr>
      <th class="text-left">Device</th>
      <th class="text-left">Total traffic</a></th>
   </tr>
  </thead>
  <tbody>
    ';

    foreach (dbFetchRows($query, $params) as $result) {
        $common_output[] = '
    <tr>
      <td class="text-left">'.generate_device_link($result, shorthost($result['hostname'])).'</td>
      <td class="text-left">'.generate_device_link(
        $result,
        generate_minigraph_image(
        $result, $config['time']['day'],
        $config['time']['now'],
        'device_bits',
        'no', 150, 21),
        array(), 0, 0, 0).'
      </td>
    </tr>
        ';
    }
    $common_output[] = '
  </tbody>
</table>
</div>
    ';
}
