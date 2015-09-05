<?php
/* Copyright (C) 2015 Daniel Preussker, QuxLabs UG <preussker@quxlabs.com>
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
 * Generic Graph Widget
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 * @package LibreNMS
 * @subpackage Widgets
 */

if( empty($widget_settings) ) {
    $common_output[] = '
<form class="form" onsubmit="widget_settings(this); return false;">
  <div class="form-group">
    <label for="device_id" class="col-sm-2 control-label">Device: </label>
    <div class="col-sm-10">
      <input type="text" class="form-control input-sm widget-device-input" name="graph_device" placeholder="Device Name">
    </div>
  </div>
  <div class="form-group">
    <label for="graph_type" class="col-sm-2 control-label">Graph: </label>
    <div class="col-sm-8">
      <select class="form-control input-sm" name="graph_type">';
    foreach (get_graph_subtypes('device') as $avail_type) {
        $common_output[] = '<option value="device_'.$avail_type.'"';
        if ($avail_type == $subtype) {
            $common_output[] = " selected";
        }
        $display_type = is_mib_graph($type, $avail_type) ? $avail_type : nicecase($avail_type);
        $common_output[] = '>'.$display_type.'</option>';
    }
    $common_output[] = '
      </select>
    </div>
    <div class="col-sm-offset-10 col-sm-2">
      <div class="checkbox input-sm">
        <label>
          <input type="checkbox" name="graph_legend" class="widget_setting" value="1"> Legend
        </label>
      </div>
    </div>
  </div>
  <div class="form-group">
    <label for="graph_width" class="col-sm-2 control-label">Width: </label>
    <div class="col-sm-10">
      <input type="number" class="form-control input-sm" name="graph_width" value="485" min="100">
    </div>
  </div>
  <div class="form-group">
    <label for="graph_height"class="col-sm-2 control-label">Height </label>
    <div class="col-sm-10">
      <input type="number" class="form-control input-sm" name="graph_height" value="100" min="100">
    </div>
  </div>
  <button type="submit" class="btn btn-default">Set</button>
</form>
<script>
graph_devices = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace("name"),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
      url: "ajax_search.php?search=%QUERY&type=device&map=1",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    name: item.name,
                };
            });
        },
      wildcard: "%QUERY"
  }
});
graph_devices.initialize();
$(".widget-device-input").typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: "typeahead-left"
    }
},
{
  source: graph_devices.ttAdapter(),
  async: true,
  displayKey: "name",
  valueKey: "name",
    templates: {
        header: "<h5><strong>&nbsp;Devices</strong></h5>",
        suggestion: Handlebars.compile("<p>&nbsp;{{name}}</p>")
    }
});
</script>';
}
else {
    $widget_settings['device_id'] = dbFetchCell('select device_id from devices where hostname = ?',array($widget_settings['graph_device']));
    $common_output[] = "<header>".$widget_settings['graph_device']." / ".$widget_settings['graph_type']."</header>";
    $common_output[] = generate_minigraph_image(array('device_id'=>(int) $widget_settings['device_id']), $config['time']['day'], $config['time']['now'], $widget_settings['graph_type'], $widget_settings['graph_legend'] == 1 ? 'yes' : 'no', $widget_settings['graph_width'], $widget_settings['graph_height'], '&', $widget_settings['graph_type']);
}
?>

