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

if( defined('show_settings') || empty($widget_settings) ) {
    $common_output[] = '
<form class="form" onsubmit="widget_settings(this); return false;">
  <div class="form-group">
    <div class="col-sm-2">
      <label for="graph_type" class="control-label">Graph: </label>
    </div>
    <div class="col-sm-8">
      <select class="form-control" name="graph_type" id="select_'.$unique_id.'" onchange="switch_'.$unique_id.'($(this).val());">';
    if (empty($widget_settings['graph_type'])) {
        $common_output[] = '<option disabled selected>Select a Graph</option>';
    }
    $sub = '';
    $old = '';
    foreach (array('device','port','application','munin') as $type) {
        $common_output[] = '<option disabled>'.nicecase($type).':</option>';
        foreach (get_graph_subtypes($type) as $avail_type) {
            $display_type = is_mib_graph($type, $avail_type) ? $avail_type : nicecase($avail_type);
            if( strstr($display_type,'_') ) {
                $sub = explode('_',$display_type,2);
                $sub = array_shift($sub);
                if( $sub != $old ) {
                    $old = $sub;
                    $common_output[] = '<option disabled>&nbsp;&nbsp;&nbsp;'.nicecase($sub).':</option>';
                }
                $display_type = str_replace($sub.'_','',$display_type);
                $space = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            } else {
                $space = '&nbsp;&nbsp;&nbsp;';
            }
            $common_output[] = '<option value="'.$type.'_'.$avail_type.'"';
            if ($type.'_'.$avail_type == $widget_settings['graph_type']) {
                $common_output[] = " selected";
            }
            $common_output[] = '>'.$space.nicecase($display_type).'</option>';
        }
        $common_output[] = '<option disabled></option>';
    }
    $common_output[] = '
        <option disabled>Port Aggregators:</option>
        <option value="transit"'.($widget_settings['graph_type'] == 'transit' ? ' selected' : '').'>&nbsp;&nbsp;&nbsp;Transit</option>
        <option value="peering"'.($widget_settings['graph_type'] == 'peering' ? ' selected' : '').'>&nbsp;&nbsp;&nbsp;Peering</option>
        <option value="core"'.($widget_settings['graph_type'] == 'core' ? ' selected' : '').'>&nbsp;&nbsp;&nbsp;Core</option>
        <option value="custom"'.($widget_settings['graph_type'] == 'custom' ? ' selected' : '').'>&nbsp;&nbsp;&nbsp;Custom Descr</option>
        <option disabled></option>
        <option value="bill_bits"'.($widget_settings['graph_type'] == 'bill_bits' ? ' selected' : '').'>Bill</option>
      </select>
    </div>
    <div class="col-sm-offset-10 col-sm-2">
      <div class="checkbox input-sm">
        <label class="control-label">
          <input type="checkbox" name="graph_legend" class="widget_setting" value="1" '.($widget_settings['graph_legend'] ? 'checked' : '').'> Legend
        </label>
      </div>
    </div>
  </div>
  <div class="clearfix"></div>
  <div class="form-group">
    <div class="col-sm-2">
      <label for="graph_range" class="control-label">Range: </label>
    </div>
    <div class="col-sm-10">
      <select class="form-control" name="graph_range">';
    $checked = '';
    foreach( array_diff_key($config['time'],array('now'=>'')) as $k=>$v ) {
        if ($widget_settings['graph_range'] == $k) {
            $checked = ' selected';
        } else {
            $checked = '';
        }
        $common_output[] = '<option value="'.$k.'"'.$checked.'>'.nicecase($k).'</option>';
    }
    $common_output[] = '
      </select>
    </div>
  </div>
  <div class="form-group input_'.$unique_id.'" id="input_'.$unique_id.'_device">
    <div class="col-sm-2">
      <label for="graph_device" class="control-label">Device: </label>
    </div>
    <div class="col-sm-10">
      <input type="text" class="form-control input_'.$unique_id.'_device" name="graph_device" placeholder="Device Name" value="'.htmlspecialchars($widget_settings['graph_device']).'">
    </div>
  </div>
  <div class="form-group input_'.$unique_id.'" id="input_'.$unique_id.'_port">
    <div class="col-sm-2">
      <label for="graph_port" class="control-label">Port: </label>
    </div>
    <div class="col-sm-10">
      <input type="text" class="form-control input_'.$unique_id.'_port" name="graph_port" placeholder="Port" value="'.htmlspecialchars($widget_settings['graph_port']).'">
    </div>
  </div>
  <div class="form-group input_'.$unique_id.'" id="input_'.$unique_id.'_application">
    <div class="col-sm-2">
      <label for="graph_application" class="control-label">Application: </label>
    </div>
    <div class="col-sm-10">
      <input type="text" class="form-control input_'.$unique_id.'_application" name="graph_application" placeholder="Application" value="'.htmlspecialchars($widget_settings['graph_application']).'">
    </div>
  </div>
  <div class="form-group input_'.$unique_id.'" id="input_'.$unique_id.'_munin">
    <div class="col-sm-2">
      <label for="graph_munin" class="control-label">Munin Plugin: </label>
    </div>
    <div class="col-sm-10">
      <input type="text" class="form-control input_'.$unique_id.'_munin" name="graph_munin" placeholder="Munin Plugin" value="'.htmlspecialchars($widget_settings['graph_munin']).'">
    </div>
  </div>
  <div class="form-group input_'.$unique_id.'" id="input_'.$unique_id.'_custom">
    <div class="col-sm-2">
      <label for="graph_munin" class="control-label">Custom Port-Desc: </label>
    </div>
    <div class="col-sm-10">
      <select class="form-control input_'.$unique_id.'_custom" name="graph_custom">';
    foreach ($config['custom_descr'] as $opt) {
        $common_output[] = '<option value="'.$opt.'" '.($widget_settings['graph_custom'] == $opt ? 'selected' : '').'>'.ucfirst($opt).'</option>';
    }
    $common_output[] = '      </select>
    </div>
  </div>
  <div class="form-group input_'.$unique_id.'" id="input_'.$unique_id.'_bill">
    <div class="col-sm-2">
      <label for="graph_bill" class="control-label">Bill: </label>
    </div>
    <div class="col-sm-10">
      <input type="text" class="form-control input_'.$unique_id.'_bill" name="graph_bill" placeholder="Bill" value="'.htmlspecialchars($widget_settings['graph_bill']).'">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-2">
      <button type="submit" class="btn btn-default">Set</button>
    </div>
  </div>
</form>
<style>
.twitter-typeahead {
  width: 100%;
}
</style>
<script>
function '.$unique_id.'() {
  var '.$unique_id.'_device = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace("name"),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: "ajax_search.php?search=%QUERY&type=device",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    device_id:       item.device_id,
                    device_image:    item.device_image,
                    name:            item.name,
                    device_os:       item.device_os,
                    version:         item.version,
                    device_hardware: item.device_hardware,
                    device_ports:    item.device_ports,
                    location:        item.location
                };
            });
        },
      wildcard: "%QUERY"
    }
  });
  '.$unique_id.'_device.initialize();
  $(".input_'.$unique_id.'_device").typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: "typeahead-left"
    }
  },
  {
    source: '.$unique_id.'_device.ttAdapter(),
    async: false,
    templates: {
      header: "<h5><strong>&nbsp;Devices</strong></h5>",
      suggestion: Handlebars.compile(\'<p><img src="{{device_image}}" border="0"> <small><strong>{{name}}</strong> | {{device_os}} | {{version}} | {{device_hardware}} with {{device_ports}} port(s) | {{location}}</small></p>\')
    }
  });

  var '.$unique_id.'_port = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace("port_id"),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: "ajax_search.php?search=%QUERY&type=ports",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    name:        item.name,
                    description: item.description,
                    hostname:    item.hostname,
                    port_id:     item.port_id
                };
            });
        },
      wildcard: "%QUERY"
    }
  });
  '.$unique_id.'_port.initialize();
  $(".input_'.$unique_id.'_port").typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: "typeahead-left"
    }
  },
  {
    source: '.$unique_id.'_port.ttAdapter(),
    async: false,
    templates: {
      header: "<h5><strong>&nbsp;Ports</strong></h5>",
      suggestion: Handlebars.compile(\'<p><small><img src="images/icons/port.png" /> <strong>{{name}}</strong> – {{hostname}} - <i>{{description}}</i></small></p>\')
    }
  });

  var '.$unique_id.'_application = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace("app_id"),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: "ajax_search.php?search=%QUERY&type=applications",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    name:        item.name,
                    hostname:    item.hostname,
                    app_id:      item.app_id
                };
            });
        },
      wildcard: "%QUERY"
    }
  });
  '.$unique_id.'_application.initialize();
  $(".input_'.$unique_id.'_application").typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: "typeahead-left"
    }
  },
  {
    source: '.$unique_id.'_application.ttAdapter(),
    async: false,
    templates: {
      header: "<h5><strong>&nbsp;Applications</strong></h5>",
      suggestion: Handlebars.compile(\'<p><small><img src="images/icons/port.png" /> <strong>{{name}}</strong> – {{hostname}}</small></p>\')
    }
  });

  var '.$unique_id.'_munin = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace("munin"),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: "ajax_search.php?search=%QUERY&type=munin",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    desc:        item.name,
                    name:        item.plugin,
                    hostname:    item.hostname,
                    plugin:      item.plugin,
                    device_id:   item.device_id,
                };
            });
        },
      wildcard: "%QUERY"
    }
  });
  '.$unique_id.'_munin.initialize();
  $(".input_'.$unique_id.'_munin").typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: "typeahead-left"
    }
  },
  {
    source: '.$unique_id.'_munin.ttAdapter(),
    async: false,
    templates: {
      header: "<h5><strong>&nbsp;Munin</strong></h5>",
      suggestion: Handlebars.compile(\'<p><small><img src="images/icons/port.png" /> <strong>{{plugin}}</strong> – {{hostname}}</small></p>\')
    }
  });

  var '.$unique_id.'_bill = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace("munin"),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: "ajax_search.php?search=%QUERY&type=bill",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    name:        item.bill_name,
                    bill_id:     item.bill_id,
                };
            });
        },
      wildcard: "%QUERY"
    }
  });
  '.$unique_id.'_bill.initialize();
  $(".input_'.$unique_id.'_bill").typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: "typeahead-left"
    }
  },
  {
    source: '.$unique_id.'_bill.ttAdapter(),
    async: false,
    templates: {
      header: "<h5><strong><i class=\'fa fa-money\'></i>&nbsp;Bill</strong></h5>",
      suggestion: Handlebars.compile(\'<p><small><strong>{{name}}</strong></small></p>\')
    }
  });

  switch_'.$unique_id.'($("#select_'.$unique_id.'").val());
}
function switch_'.$unique_id.'(data) {
    $(".input_'.$unique_id.'").fadeOut();
    if (data != undefined && data != "") {
        data = data.split("_");
        type = data.shift();
        data = data.join("_");
        $("#input_'.$unique_id.'_"+type).fadeIn();
    }
}
</script>
<script id="js_'.$unique_id.'">
$(function() {
  $("#js_'.$unique_id.'").parent().parent().data("settings","1");
  '.$unique_id.'();
});
</script>';
}
else {
    $widget_settings['title']         = "";
    $type                             = explode('_',$widget_settings['graph_type'],2);
    $type                             = array_shift($type);
    $widget_settings['graph_'.$type] = json_decode($widget_settings['graph_'.$type],true)?:$widget_settings['graph_'.$type];
    if ($type == 'device') {
        $widget_settings['title']     = $widget_settings['graph_device']['name']." / ".$widget_settings['graph_type'];
        $param                        = 'device='.$widget_settings['graph_device']['device_id'];
    }
    elseif ($type == 'application') {
        $param                        = 'id='.$widget_settings['graph_'.$type]['app_id'];
    }
    elseif ($type == 'munin') {
        $param                        = 'device='.$widget_settings['graph_'.$type]['device_id'].'&plugin='.$widget_settings['graph_'.$type]['name'];
    }
    elseif ($type == 'transit' || $type == 'peering' || $type == 'core') {
        $type_where = ' (';
        if (is_array($config[$type.'_descr']) === false) {
            $config[$type.'_descr'] = array($config[$type.'_descr']);
        }
        foreach ($config[$type.'_descr'] as $additional_type) {
            if (!empty($additional_type)) {
                $type_where  .= " $or `port_descr_type` = ?";
                $or           = 'OR';
                $type_param[] = $additional_type;
            }
        }
        $type_where  .= " $or `port_descr_type` = ?";
        $or           = 'OR';
        $type_param[] = $type;
        $type_where .= ') ';
        foreach (dbFetchRows("SELECT port_id FROM `ports` WHERE $type_where ORDER BY ifAlias", $type_param) as $port) {
            $tmp[] = $port['port_id'];
        }
        $param                        = 'id='.implode(',',$tmp);
        $widget_settings['graph_type']= 'multiport_bits_separate';
        $widget_settings['title']     = 'Overall '.ucfirst($type).' Bits ('.$widget_settings['graph_range'].')';
    }
    elseif ($type == 'custom') {
        foreach (dbFetchRows("SELECT port_id FROM `ports` WHERE `port_descr_type` = ? ORDER BY ifAlias", array($widget_settings['graph_custom'])) as $port) {
            $tmp[] = $port['port_id'];
        }
        $param                        = 'id='.implode(',',$tmp);
        $widget_settings['graph_type']= 'multiport_bits_separate';
        $widget_settings['title']     = 'Overall '.ucfirst(htmlspecialchars($widget_settings['graph_custom'])).' Bits ('.$widget_settings['graph_range'].')';
    }
    else {
        $param                        = 'id='.$widget_settings['graph_'.$type][$type.'_id'];
    }
    if (empty($widget_settings['title'])) {
        $widget_settings['title']     = $widget_settings['graph_'.$type]['hostname']." / ".$widget_settings['graph_'.$type]['name']." / ".$widget_settings['graph_type'];
    }
    $common_output[]                  = '<img class="minigraph-image" width="'.$widget_dimensions['x'].'" height="'.$widget_dimensions['y'].'" src="graph.php?'.$param.'&from='.$config['time'][$widget_settings['graph_range']].'&to='.$config['time']['now'].'&width='.$widget_dimensions['x'].'&height='.$widget_dimensions['y'].'&type='.$widget_settings['graph_type'].'&legend='.($widget_settings['graph_legend'] == 1 ? 'yes' : 'no').'&absolute=1"/>';
}
