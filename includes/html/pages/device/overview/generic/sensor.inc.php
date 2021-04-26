<?php

$sensors = dbFetchRows('SELECT * FROM `sensors` WHERE `sensor_class` = ? AND device_id = ? ORDER BY `group`, `sensor_descr`, `sensor_oid`, `sensor_index`', [$sensor_class, $device['device_id']]);

if (count($sensors)) {
    $icons = \App\Models\Sensor::getIconMap();
    $sensor_fa_icon = 'fa-' . (isset($icons[$sensor_class]) ? $icons[$sensor_class] : 'delicious');

    echo '
        <div class="row">
        <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
        <div class="panel-heading">';
    echo '<a href="device/device=' . $device['device_id'] . '/tab=health/metric=' . strtolower($sensor_type) . '/"><i class="fa ' . $sensor_fa_icon . ' fa-lg icon-theme" aria-hidden="true"></i><strong> ' . \LibreNMS\Util\StringHelpers::niceCase($sensor_type) . '</strong></a>';
    echo '      </div>
        <table class="table table-hover table-condensed table-striped">';
    $group = '';
    foreach ($sensors as $sensor) {
        if (! isset($sensor['sensor_current'])) {
            $sensor['sensor_current'] = 'NaN';
        }

        if ($group != $sensor['group']) {
            $group = $sensor['group'];
            echo "<tr><td colspan='3'><strong>$group</strong></td></tr>";
        }

        // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
        // FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
        // FIXME - DUPLICATED IN health/sensors
        $graph_colour = str_replace('#', '', $row_colour);

        $graph_array = [];
        $graph_array['height'] = '100';
        $graph_array['width'] = '210';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $sensor['sensor_id'];
        $graph_array['type'] = $graph_type;
        $graph_array['from'] = \LibreNMS\Config::get('time.day');
        $graph_array['legend'] = 'no';

        $link_array = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width'], $link_array['legend']);
        $link = \LibreNMS\Util\Url::generate($link_array);

        if ($sensor['poller_type'] == 'ipmi') {
            $sensor['sensor_descr'] = substr(ipmiSensorName($device['hardware'], $sensor['sensor_descr']), 0, 48);
        } else {
            $sensor['sensor_descr'] = substr($sensor['sensor_descr'], 0, 48);
        }

        $overlib_content = '<div class=overlib><span class=overlib-text>' . $device['hostname'] . ' - ' . $sensor['sensor_descr'] . '</span><br />';
        foreach (['day', 'week', 'month', 'year'] as $period) {
            $graph_array['from'] = \LibreNMS\Config::get("time.$period");
            $overlib_content .= str_replace('"', "\'", \LibreNMS\Util\Url::graphTag($graph_array));
        }

        $overlib_content .= '</div>';

        $graph_array['width'] = 80;
        $graph_array['height'] = 20;
        $graph_array['bg'] = 'ffffff00';
        // the 00 at the end makes the area transparent.
        $graph_array['from'] = \LibreNMS\Config::get('time.day');
        $sensor_minigraph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

        $sensor_current = $graph_type == 'sensor_state' ? get_state_label($sensor) : get_sensor_label_color($sensor);

        echo '<tr>
            <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, \LibreNMS\Util\Rewrite::shortenIfType($sensor['sensor_descr']), $overlib_content, $sensor_class) . '</td>
            <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, $sensor_minigraph, $overlib_content, $sensor_class) . '</td>
            <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, $sensor_current, $overlib_content, $sensor_class) . '</td>
            </tr>';
    }//end foreach

    echo '</table>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}//end if
