<?php

$processors = dbFetchRows('SELECT * FROM `processors` WHERE device_id = ?', array($device['device_id']));

if (count($processors)) {
    echo '<div class="container-fluid ">
      <div class="row">
        <div class="col-md-12 ">
          <div class="panel panel-default panel-condensed">
            <div class="panel-heading">
';
    echo '<a href="device/device='.$device['device_id'].'/tab=health/metric=processor/">';
    echo "<img src='images/icons/processor.png'> <strong>Processors</strong></a>";
    echo '</div>
        <table class="table table-hover table-condensed table-striped">';

    $graph_array           = array();
    $graph_array['to']     = $config['time']['now'];
    $graph_array['type']   = 'processor_usage';
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = 'no';

    $totalPercent=0;

    foreach ($processors as $proc) {
        $text_descr = rewrite_entity_descr($proc['processor_descr']);

        $percent      = $proc['processor_usage'];
        if ($config['cpu_details_overview'] === true)
        {

            $background   = get_percentage_colours($percent);

            $graph_array['id']     = $proc['processor_id'];

            //Generate tooltip graphs
            $graph_array['height'] = '100';
            $graph_array['width']  = '210';
            $link_array         = $graph_array;
            $link_array['page'] = 'graphs';
            unset($link_array['height'], $link_array['width'], $link_array['legend']);
            $link = generate_url($link_array);
            $overlib_content = generate_overlib_content($graph_array, $device['hostname'].' - '.$text_descr);

            //Generate the minigraph
            $graph_array['width']  = 80;
            $graph_array['height'] = 20;
            $graph_array['bg']     = 'ffffff00'; // the 00 at the end makes the area transparent.
            $minigraph =  generate_lazy_graph_tag($graph_array);

            echo '<tr>
                <td>'.overlib_link($link, $text_descr, $overlib_content).'</td>
                <td>'.overlib_link($link, $minigraph, $overlib_content).'</td>
                <td>'.overlib_link($link, print_percentage_bar(200, 20, $percent, null, 'ffffff', $background['left'], $percent.'%', 'ffffff', $background['right']), $overlib_content).'
                </a></td>
              </tr>';
        }
        else {
            $totalPercent = $totalPercent + $percent;
        }

    }//end foreach

    if ($config['cpu_details_overview'] === false)
    {

        if($_SESSION['screen_width']) {
            if($_SESSION['screen_width'] > 970) {
                $graph_array['width'] = round(($_SESSION['screen_width'] - 390 )/2,0);
                $graph_array['height'] = round($graph_array['width'] /3);
                $graph_array['lazy_w'] = $graph_array['width'] + 80;
            }
            else {
                $graph_array['width'] = $_SESSION['screen_width'] - 190;
                $graph_array['height'] = round($graph_array['width'] /3);
                $graph_array['lazy_w'] = $graph_array['width'] + 80;
            }
        }

        //Generate average cpu graph
        $graph_array['device'] = $device['device_id'];
        $graph_array['type']   = 'device_processor';
        $graph = generate_lazy_graph_tag($graph_array);

        //Generate link to graphs
        $link_array         = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width']);
        $link = generate_url($link_array);

        //Generate tooltip
        $graph_array['width']=210;
        $graph_array['height']=100;
        $overlib_content      = generate_overlib_content($graph_array, $device['hostname'].' - CPU usage');

        echo '<tr>
              <td colspan="4">';
        echo overlib_link($link, $graph, $overlib_content, null);
        echo '  </td>
            </tr>';

        //Add a row with CPU desc, count and percent graph
        $totalPercent=$totalPercent/count($processors);
        $background   = get_percentage_colours($totalPercent);

         echo '<tr>
             <td>'.overlib_link($link, $text_descr, $overlib_content).'</td>
             <td>'.overlib_link($link,'x'.count($processors),$overlib_content).'</td>
             <td>'.overlib_link($link, print_percentage_bar(200, 20, $totalPercent, null, 'ffffff', $background['left'], $percent.'%', 'ffffff', $background['right']), $overlib_content).'</td>
           </tr>';

    }

    echo '</table>
        </div>
        </div>
        </div>
        </div>';
}//end if
