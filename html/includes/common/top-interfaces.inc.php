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
 * Top interfaces by traffic
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
      <label for="graph_type" class="control-label">Number of Interfaces: </label>
    </div>
    <div class="col-sm-4">
      <input class="form-control" name="interface_count" id="input_'.$unique_id.'" placeholder="ie. 5" value="'.$widget_settings['interface_count'].'">
    </div>
  </div>
  <div class="clearfix"></div>
  <div class="form-group">
    <div class="col-sm-6">
      <label for="graph_type" class="control-label">Time interval (minutes): </label>
    </div>
    <div class="col-sm-4">
      <input class="form-control" name="time_interval" id="input_'.$unique_id.'" placeholder="ie. 15" value="'.$widget_settings['time_interval'].'">
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
    $interval_seconds = ($interval * 60);
    $interface_count = $widget_settings['interface_count'];
    $common_output[] = '
<h4>Top '.$interface_count.' interfaces (last '.$interval.' minutes)</h4>
    ';
    if (is_admin() || is_read()) {
        $query = '
            SELECT *, p.ifInOctets_rate + p.ifOutOctets_rate as total
            FROM ports as p, devices as d
            WHERE d.device_id = p.device_id
            AND unix_timestamp() - p.poll_time < '.$interval_seconds.' 
            AND ( p.ifInOctets_rate > 0
            OR p.ifOutOctets_rate > 0 )
            ORDER BY total desc
            LIMIT '.$interface_count.'
        ';
    }
    else {
        $query = '
            SELECT *, I.ifInOctets_rate + I.ifOutOctets_rate as total
            FROM ports as I, devices as d,
            `devices_perms` AS `P`, `ports_perms` AS `PP`
            WHERE ((`P`.`user_id` = ? AND `P`.`device_id` = `d`.`device_id`) 
            OR (`PP`.`user_id` = ? AND `PP`.`port_id` = `I`.`port_id` 
            AND `I`.`device_id` = `d`.`device_id`)) AND
            d.device_id = I.device_id
            AND unix_timestamp() - I.poll_time < '.$interval_seconds.' 
            AND ( I.ifInOctets_rate > 0
            OR I.ifOutOctets_rate > 0 )
            ORDER BY total desc
            LIMIT '.$interface_count.'
        ';
    }
    
    $common_output[] = '
<div class="table-responsive">
<table class="table table-hover table-condensed table-striped bootgrid-table">
  <thead>
    <tr>
      <th class="text-left">Device</th>
      <th class="text-left">Interface</th>
      <th class="text-left">Total traffic</a></th>
   </tr>
  </thead>
  <tbody>
    ';

    foreach (dbFetchRows($query, array($_SESSION['user_id'])) as $result) {
        $common_output[] = '
    <tr>
      <td class="text-left">'.generate_device_link($result, shorthost($result['hostname'])).'</td>
      <td class="text-left">'.generate_port_link($result).'</td>
      <td class="text-left">'.generate_port_link($result, generate_port_thumbnail($result)).'</td>
    </tr>
        ';
    }
    $common_output[] = '
  </tbody>
</table>
</div>
    ';
}
