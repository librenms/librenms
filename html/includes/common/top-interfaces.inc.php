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
<form class="form-horizontal" onsubmit="widget_settings(this); return false;">
  <div class="form-group">
    <label for="interface_count" class="col-sm-5 control-label">Number of Interfaces: </label>
    <div class="col-sm-7">
      <input class="form-control" type="number" min="0" step="1" name="interface_count" id="input_count_'.$unique_id.'" placeholder="ie. 5" value="'.$widget_settings['interface_count'].'">
    </div>
  </div>
  <div class="form-group">
    <label for="time_interval" class="col-sm-5 control-label">Last Polled within (minutes): </label>
    <div class="col-sm-7">
      <input class="form-control" type="number" min="5" step="1" name="time_interval" id="input_time_'.$unique_id.'" placeholder="ie. 15" value="'.$widget_settings['time_interval'].'">
    </div>
  </div>
  <div class="form-group">
    <label for="interface_filter" class="col-sm-5 control-label">Interface Type: </label>
    <div class="col-sm-7">
      <input class="form-control" name="interface_filter" id="input_filter_'.$unique_id.'" placeholder="Any" value="'.$widget_settings['interface_filter'].'">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-5 col-sm-7">
      <button type="submit" class="btn btn-default">Set</button>
    </div>
  </div>
</form>
<script>
$(function() {
  var '.$unique_id.'_filter = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace("name"),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: "ajax_search.php?search=%QUERY&type=iftype",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    filter:        item.filter
                };
            });
        },
      wildcard: "%QUERY"
    }
  });
  '.$unique_id.'_filter.initialize();
  $("#input_filter_'.$unique_id.'").typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: "typeahead-left"
    }
  },
  {
    source: '.$unique_id.'_filter.ttAdapter(),
    async: false,
    display: "filter",
    templates: {
      header: "<h5><strong>&nbsp;Interface Types</strong></h5>",
      suggestion: Handlebars.compile("<p>{{filter}}</p>")
    }
  });
});
</script>
<style>
.twitter-typeahead {
  width: 100%;
}
</style>
    ';
}
else {
    $interval = $widget_settings['time_interval'];
    (integer) $lastpoll_seconds = ($interval * 60);
    (integer) $interface_count = $widget_settings['interface_count'];
    $params = array('user' => $_SESSION['user_id'], 'lastpoll' => array($lastpoll_seconds), 'count' => array($interface_count), 'filter' => ($widget_settings['interface_filter']?:(int)1));
    if (is_admin() || is_read()) {
        $query = '
            SELECT *, p.ifInOctets_rate + p.ifOutOctets_rate as total
            FROM ports as p
            INNER JOIN devices ON p.device_id = devices.device_id
            AND unix_timestamp() - p.poll_time <= :lastpoll
            AND ( p.ifType = :filter || 1 = :filter )
            AND ( p.ifInOctets_rate > 0 || p.ifOutOctets_rate > 0 )
            ORDER BY total DESC
            LIMIT :count
        ';
    }
    else {
        $query = '
            SELECT ports.*, devices.hostname, ports.ifInOctets_rate + ports.ifOutOctets_rate as total
            FROM ports
            INNER JOIN devices ON ports.device_id = devices.device_id
            LEFT JOIN ports_perms ON ports.port_id = ports_perms.port_id
            LEFT JOIN devices_perms ON devices.device_id = devices_perms.device_id
            WHERE ( ports_perms.user_id = :user || devices_perms.user_id = :user )
            AND unix_timestamp() - ports.poll_time <= :lastpoll
            AND ( ports.ifType = :filter || 1 = :filter )
            AND ( ports.ifInOctets_rate > 0 || ports.ifOutOctets_rate > 0 )
            GROUP BY ports.port_id
            ORDER BY total DESC
            LIMIT :count
            ';
    }
    $common_output[] = '
<h4>Top '.$interface_count.' interfaces polled within '.$interval.' minutes</h4>
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

    foreach (dbFetchRows($query, $params) as $result) {
        $common_output[] = '
    <tr>
      <td class="text-left">'.generate_device_link($result, shorthost($result['hostname'])).'</td>
      <td class="text-left">'.generate_port_link($result, shorten_interface_type($result['ifName'])).'</td>
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
