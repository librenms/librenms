<?php

echo '<div class="panel panel-default panel-condensed">';
echo '<div class="panel-heading">';
echo $displayLists;
echo '</div>';
echo '<div class="panel-body">';

foreach ($ports as $port) {
    $speed = humanspeed($port['ifSpeed']);
    $type  = humanmedia($port['ifType']);

    $port['in_rate']  = formatRates(($port['ifInOctets_rate'] * 8));
    $port['out_rate'] = formatRates(($port['ifOutOctets_rate'] * 8));

    if ($port['in_errors'] > 0 || $port['out_errors'] > 0) {
        $error_img = generate_port_link($port, "<i class='fa fa-flag fa-lg' style='color:red' aria-hidden='true'></i>", 'errors');
    } else {
        $error_img = '';
    }

    if (port_permitted($port['port_id'], $port['device_id'])) {
        $port = cleanPort($port, $device);

        $graph_type = 'port_'.$subformat;

        if (session('widescreen')) {
            $width = 357;
        } else {
            $width = 315;
        }

        if (session('widescreen')) {
            $width_div = 438;
        } else {
            $width_div = 393;
        }

        $graph_array           = array();
        $graph_array['height'] = 100;
        $graph_array['width']  = 210;
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id']     = $port['port_id'];
        $graph_array['type']   = $graph_type;
        $graph_array['from'] = \LibreNMS\Config::get('time.day');
        $graph_array['legend'] = 'no';

        $link_array         = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width'], $link_array['legend']);
        $link                  = generate_url($link_array);
        $overlib_content       = generate_overlib_content($graph_array, $port['hostname'].' - '.$port['label']);
        $graph_array['title']  = 'yes';
        $graph_array['width']  = $width;
        $graph_array['height'] = 119;
        $graph =  generate_lazy_graph_tag($graph_array);

        echo "<div class='graph-all-common' style='min-width: ".$width_div.'px;max-width:'.$width_div."px;'>";
        echo overlib_link($link, $graph, $overlib_content);
        echo '</div>';
    }
}

echo '</div>';
