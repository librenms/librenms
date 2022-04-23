<?php

use LibreNMS\Util\StringHelpers;

$graph_type = 'toner_usage';

$supplies = \App\Models\PrinterSupply::query()->where('device_id', $device['device_id'])->get()->groupBy('supply_type');

foreach ($supplies as $type => $supply) {
    if (! empty($supply)) {
        echo '
          <div class="row">
          <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
              <div class="panel-heading">';
        echo '<a href="device/device=' . $device['device_id'] . '/tab=printer/">';
        $title = StringHelpers::camelToTitle($type == 'opc' ? 'organicPhotoConductor' : $type);
        echo '<i class="fa fa-print fa-lg icon-theme" aria-hidden="true"></i> <strong>' . $title . '</strong></a>';
        echo '</div>
        <table class="table table-hover table-condensed table-striped">';

        foreach ($supply as $toner) {
            $percent = round($toner['supply_current']);
            $background = toner2colour($toner['supply_descr'], $percent);

            $graph_array = [
                'height' => 100,
                'width' => 210,
                'to' => \LibreNMS\Config::get('time.now'),
                'id' => $toner['supply_id'],
                'type' => $graph_type,
                'from' => \LibreNMS\Config::get('time.day'),
                'legend' => 'no',
            ];

            $link_array = $graph_array;
            $link_array['page'] = 'graphs';
            unset($link_array['height'], $link_array['width'], $link_array['legend']);
            $link = \LibreNMS\Util\Url::generate($link_array);

            $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . ' - ' . $toner['supply_descr']);

            $graph_array['width'] = 80;
            $graph_array['height'] = 20;
            $graph_array['bg'] = 'ffffff00';
            // the 00 at the end makes the area transparent.
            $minigraph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

            echo '<tr>
            <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, $toner['supply_descr'], $overlib_content) . '</td>
            <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, $minigraph, $overlib_content) . '</td>
            <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, print_percentage_bar(200, 20, $percent, null, 'ffffff', $background['left'], $percent . '%', 'ffffff', $background['right']), $overlib_content) . '
           </a></td>
         </tr>';
        }//end foreach

        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }//end if
}

unset($toner_rows);
