<?php

$processors = dbFetchRows('SELECT * FROM `processors` WHERE device_id = ?', [$device['device_id']]);

if (count($processors)) {
    echo '
      <div class="row">
        <div class="col-md-12 ">
          <div class="panel panel-default panel-condensed">
            <div class="panel-heading">
';
    echo '<a href="device/device=' . $device['device_id'] . '/tab=health/metric=processor/">';
    echo '<i class="fa fa-microchip fa-lg icon-theme" aria-hidden="true"></i> <strong>Processors</strong></a>';
    echo '</div>
        <table class="table table-hover table-condensed table-striped">';

    $graph_array = [];
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['type'] = 'processor_usage';
    $graph_array['from'] = \LibreNMS\Config::get('time.day');
    $graph_array['legend'] = 'no';

    $total_percent = [];

    foreach ($processors as $proc) {
        $text_descr = rewrite_entity_descr($proc['processor_descr']);

        $percent = $proc['processor_usage'];
        if (\LibreNMS\Config::get('cpu_details_overview') === true) {
            $background = get_percentage_colours($percent, $proc['processor_perc_warn']);

            $graph_array['id'] = $proc['processor_id'];

            //Generate tooltip graphs
            $graph_array['height'] = '100';
            $graph_array['width'] = '210';
            $link_array = $graph_array;
            $link_array['page'] = 'graphs';
            unset($link_array['height'], $link_array['width'], $link_array['legend']);
            $link = generate_url($link_array);
            $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . ' - ' . $text_descr);

            //Generate the minigraph
            $graph_array['width'] = 80;
            $graph_array['height'] = 20;
            $graph_array['bg'] = 'ffffff00'; // the 00 at the end makes the area transparent.
            $minigraph = generate_lazy_graph_tag($graph_array);

            echo '<tr>
                <td class="col-md-4">' . overlib_link($link, $text_descr, $overlib_content) . '</td>
                <td class="col-md-4">' . overlib_link($link, $minigraph, $overlib_content) . '</td>
                <td class="col-md-4">' . overlib_link($link, print_percentage_bar(200, 20, $percent, null, 'ffffff', $background['left'], $percent . '%', 'ffffff', $background['right']), $overlib_content) . '
                </a></td>
              </tr>';
        } else {
            if (! isset($total_percent[$proc['processor_type']])) {
                $total_percent[$proc['processor_type']] = [
                    'usage' => 0,
                    'warn' => 0,
                    'descr' => $text_descr,
                    'count' => 0,
                ];
            }
            $total_percent[$proc['processor_type']]['usage'] += $percent;
            $total_percent[$proc['processor_type']]['warn'] += $proc['processor_perc_warn'];
            $total_percent[$proc['processor_type']]['count'] += 1;
        }
    }//end foreach

    if (\LibreNMS\Config::get('cpu_details_overview') === false) {
        if ($screen_width = Session::get('screen_width')) {
            if ($screen_width > 970) {
                $graph_array['width'] = round(($screen_width - 390) / 2, 0);
                $graph_array['height'] = round($graph_array['width'] / 3);
                $graph_array['lazy_w'] = $graph_array['width'] + 80;
            } else {
                $graph_array['width'] = $screen_width - 190;
                $graph_array['height'] = round($graph_array['width'] / 3);
                $graph_array['lazy_w'] = $graph_array['width'] + 80;
            }
        }

        //Generate average cpu graph
        $graph_array['device'] = $device['device_id'];
        $graph_array['type'] = 'device_processor';
        $graph = generate_lazy_graph_tag($graph_array);

        //Generate link to graphs
        $link_array = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width']);
        $link = generate_url($link_array);

        //Generate tooltip
        $graph_array['width'] = 210;
        $graph_array['height'] = 100;
        $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . ' - CPU usage');

        echo '<tr>
              <td colspan="4">';
        echo overlib_link($link, $graph, $overlib_content, null);
        echo '  </td>
            </tr>';
        foreach ($total_percent as $type => $values) {
            //Add a row with CPU desc, count and percent graph
            $percent_usage = ceil($values['usage'] / $values['count']);
            $percent_warn = $values['warn'] / $values['count'];
            $background = get_percentage_colours($percent_usage, $percent_warn);

            echo '
              <tr>
                <td class="col-md-4">' . overlib_link($link, $values['descr'], $overlib_content) . '</td>
                <td class="col-md-4">' . overlib_link($link, 'x' . $values['count'], $overlib_content) . '</td>
                <td class="col-md-4">' . overlib_link($link, print_percentage_bar(200, 20, $percent_usage, null, 'ffffff', $background['left'], $percent_usage . '%', 'ffffff', $background['right']), $overlib_content) . '</td>
              </tr>';
        }
    }

    echo '</table>
        </div>
        </div>
        </div>';
}//end if
