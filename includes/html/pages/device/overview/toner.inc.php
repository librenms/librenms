<?php

use LibreNMS\Util\StringHelpers;

$graph_type = 'toner_usage';

$supplies = \App\Models\PrinterSupply::query()->where('device_id', $device['device_id'])->get()->groupBy('supply_type');

foreach ($supplies as $type => $supply) {
    if (! empty($supply)) {
        echo '
            <div class="overview-panel tw:mb-5">
              <div class="tw:px-4 tw:py-2.5 tw:bg-[#f5f5f5] tw:border-b tw:border-gray-300 tw:text-[#333] tw:dark:bg-dark-gray-200 tw:dark:border-[#1c1e22] tw:dark:text-dark-white-200">';
        echo '<a href="device/device=' . $device['device_id'] . '/tab=printer/">';
        $title = StringHelpers::camelToTitle($type == 'opc' ? 'organicPhotoConductor' : $type);
        echo '<i class="fa fa-print fa-lg icon-theme" aria-hidden="true"></i> <strong>' . $title . '</strong></a>';
        echo '</div>
        <div class="tw:flex tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-[#1c1e22]">';

        foreach ($supply as $toner) {
            $percent = round($toner['supply_current']);
            $background = toner2colour($toner['supply_descr'], $percent);

            $graph_array = [
                'height' => 100,
                'width' => 210,
                'to' => \App\Facades\LibrenmsConfig::get('time.now'),
                'id' => $toner['supply_id'],
                'type' => $graph_type,
                'from' => \App\Facades\LibrenmsConfig::get('time.day'),
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

            echo '<div class="tw:grid tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-[#f5f5f5] tw:dark:hover:bg-dark-gray-300 tw:grid-cols-3">
            <div>' . \LibreNMS\Util\Url::overlibLink($link, $toner['supply_descr'], $overlib_content) . '</div>
            <div>' . \LibreNMS\Util\Url::overlibLink($link, $minigraph, $overlib_content) . '</div>
            <div>' . \LibreNMS\Util\Url::overlibLink($link, \LibreNMS\Util\Html::percentageBar(200, 10, $percent, null, $percent . '%', null, null, [
                'left' => $background['left'],
                'left_text' => null,
                'right' => $background['right'],
                'right_text' => null,
            ]), $overlib_content) . '
           </div>
         </div>';
        }//end foreach

        echo '</div>';
        echo '</div>';
    }//end if
}

unset($toner_rows);
