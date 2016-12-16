<?php
if ($sensor_class == 'state') {
    $sensors = dbFetchRows('SELECT `sensors`.* FROM `sensors` LEFT JOIN `sensors_to_state_indexes` ON sensors_to_state_indexes.sensor_id = sensors.sensor_id LEFT JOIN state_indexes ON state_indexes.state_index_id = sensors_to_state_indexes.state_index_id WHERE `sensor_class` = ? AND device_id = ? ORDER BY `sensor_type`, `sensor_index`+0, `sensor_oid`', array($sensor_class, $device['device_id']));
} else {
    $sensors = dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND device_id = ? ORDER BY `poller_type`, `sensor_oid`, `sensor_index`', array($sensor_class, $device['device_id']));
}

if (count($sensors)) {
    echo '<div class="container-fluid ">
        <div class="row">
        <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
        <div class="panel-heading">';
    echo '<a href="device/device='.$device['device_id'].'/tab=health/metric='.strtolower($sensor_type).'/"><img src="images/icons/'.strtolower($sensor_type).'.png"><strong> '.$sensor_type.'</strong></a>';
    echo '      </div>
        <table class="table table-hover table-condensed table-striped">';
    foreach ($sensors as $sensor) {
        $state_translation = array();
        if (!empty($sensor['state_index_id'])) {
            $state_translation = dbFetchRows('SELECT * FROM `state_translations` WHERE `state_index_id` = ? AND `state_value` = ? ', array($sensor['state_index_id'], $sensor['sensor_current']));
        }

        if (!isset($sensor['sensor_current'])) {
            $sensor['sensor_current'] = 'NaN';
        }

        // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
        // FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
        // FIXME - DUPLICATED IN health/sensors
        $graph_colour = str_replace('#', '', $row_colour);

        $graph_array           = array();
        $graph_array['height'] = '100';
        $graph_array['width']  = '210';
        $graph_array['to']     = $config['time']['now'];
        $graph_array['id']     = $sensor['sensor_id'];
        $graph_array['type']   = $graph_type;
        $graph_array['from']   = $config['time']['day'];
        $graph_array['legend'] = 'no';

        $link_array         = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width'], $link_array['legend']);
        $link = generate_url($link_array);

        if ($sensor['poller_type'] == "ipmi") {
            $sensor['sensor_descr'] = substr(ipmiSensorName($device['hardware'], $sensor['sensor_descr'], $ipmiSensorsNames), 0, 48);
        } else {
            $sensor['sensor_descr'] = substr($sensor['sensor_descr'], 0, 48);
        }

        $overlib_content = '<div style="width: 580px;"><h2>'.$device['hostname'].' - '.$sensor['sensor_descr'].'</h1>';
        foreach (array('day', 'week', 'month', 'year') as $period) {
            $graph_array['from']  = $config['time'][$period];
            $overlib_content .= str_replace('"', "\'", generate_graph_tag($graph_array));
        }

        $overlib_content .= '</div>';

        $graph_array['width']  = 80;
        $graph_array['height'] = 20;
        $graph_array['bg']     = 'ffffff00';
        // the 00 at the end makes the area transparent.
        $graph_array['from'] = $config['time']['day'];
        $sensor_minigraph =  generate_lazy_graph_tag($graph_array);

        if (!empty($state_translation['0']['state_descr'])) {
            $state_style="";
            switch ($state_translation['0']['state_generic_value']) {
                case 0: // OK
                    $state_style="class='label label-success'";
                    break;
                case 1: // Warning
                    $state_style="class='label label-warning'";
                    break;
                case 2: // Critical
                    $state_style="class='label label-danger'";
                    break;
                case 3: // Unknown
                default:
                    $state_style="class='label label-default'";
                    break;
            }
            echo '<tr>
                <td class="col-md-4">'.overlib_link($link, shorten_interface_type($sensor['sensor_descr']), $overlib_content, $sensor_class).'</td>
                <td class="col-md-4">'.overlib_link($link, $sensor_minigraph, $overlib_content, $sensor_class).'</td>
                <td class="col-md-4">'.overlib_link($link, '<span '.$state_style.'>'.$state_translation['0']['state_descr'].'</span>', $overlib_content, $sensor_class).'</td>
                </tr>';
        } else {
            echo '<tr>
                <td class="col-md-4">'.overlib_link($link, shorten_interface_type($sensor['sensor_descr']), $overlib_content, $sensor_class).'</td>
                <td class="col-md-4">'.overlib_link($link, $sensor_minigraph, $overlib_content, $sensor_class).'</td>
                <td class="col-md-4">'.overlib_link($link, '<span '.($sensor['sensor_current'] < $sensor['sensor_limit_low'] || $sensor['sensor_current'] > $sensor['sensor_limit'] ? "style='color: red'" : '').'>'.$sensor['sensor_current'].$sensor_unit.'</span>', $overlib_content, $sensor_class).'</td>
                </tr>';
        }
    }//end foreach

    echo '</table>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}//end if
