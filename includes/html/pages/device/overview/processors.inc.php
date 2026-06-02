<?php

$processors = dbFetchRows('SELECT * FROM `processors` WHERE device_id = ?', [$device['device_id']]);

if (count($processors)) {
    echo '
          <div class="overview-panel tw:mb-5">
            <div class="tw:px-4 tw:py-2.5 tw:bg-[#f5f5f5] tw:border-b tw:border-[#ddd] tw:text-[#333] tw:dark:bg-dark-gray-200 tw:dark:border-[#1c1e22] tw:dark:text-dark-white-200">
';
    echo '<a href="device/device=' . $device['device_id'] . '/tab=health/metric=processor/">';
    echo '<i class="fa fa-microchip fa-lg icon-theme" aria-hidden="true"></i> <strong>Processors</strong></a>';
    echo '</div>
        <div class="tw:flex tw:flex-col tw:bg-white tw:divide-y tw:divide-[#ddd] tw:dark:bg-dark-gray-400 tw:dark:divide-[#1c1e22]">';

    $graph_array = [];
    $graph_array['to'] = \App\Facades\LibrenmsConfig::get('time.now');
    $graph_array['type'] = 'processor_usage';
    $graph_array['from'] = \App\Facades\LibrenmsConfig::get('time.day');
    $graph_array['legend'] = 'no';

    $total_percent = [];

    foreach ($processors as $proc) {
        $text_descr = rewrite_entity_descr($proc['processor_descr']);

        $percent = $proc['processor_usage'];
        if (\App\Facades\LibrenmsConfig::get('cpu_details_overview') === true) {
            $background = \LibreNMS\Util\Color::percentage($percent, $proc['processor_perc_warn']);

            $graph_array['id'] = $proc['processor_id'];

            //Generate tooltip graphs
            $graph_array['height'] = '100';
            $graph_array['width'] = '210';
            $link_array = $graph_array;
            $link_array['page'] = 'graphs';
            unset($link_array['height'], $link_array['width'], $link_array['legend']);
            $link = \LibreNMS\Util\Url::generate($link_array);
            $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . ' - ' . $text_descr);

            //Generate the minigraph
            $graph_array['width'] = 80;
            $graph_array['height'] = 20;
            $graph_array['bg'] = 'ffffff00'; // the 00 at the end makes the area transparent.
            $minigraph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

            echo '<div class="tw:grid tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-[#f5f5f5] tw:dark:hover:bg-dark-gray-300 tw:grid-cols-3">
                <div>' . \LibreNMS\Util\Url::overlibLink($link, $text_descr, $overlib_content) . '</div>
                <div>' . \LibreNMS\Util\Url::overlibLink($link, $minigraph, $overlib_content) . '</div>
                <div>' . \LibreNMS\Util\Url::overlibLink($link, \LibreNMS\Util\Html::percentageBar(200, 10, $percent, null, $percent . '%', null, null, [
                'left' => $background['left'],
                'left_text' => null,
                'right' => $background['right'],
                'right_text' => null,
            ]), $overlib_content) . '
                </div>
              </div>';
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

    if (\App\Facades\LibrenmsConfig::get('cpu_details_overview') === false) {
        $graph_array = \App\Http\Controllers\Device\Tabs\OverviewController::setGraphWidth($graph_array);

        //Generate average cpu graph
        $graph_array['device'] = $device['device_id'];
        $graph_array['type'] = 'device_processor';
        $graph = \LibreNMS\Util\Url::lazyGraphTag($graph_array, 'tw:w-full tw:h-auto');

        //Generate link to graphs
        $link_array = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width']);
        $link = \LibreNMS\Util\Url::generate($link_array);

        //Generate tooltip
        $graph_array['width'] = 210;
        $graph_array['height'] = 100;
        $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . ' - CPU usage');

        echo '<div class="tw:grid tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-[#f5f5f5] tw:dark:hover:bg-dark-gray-300">
              <div>';
        echo \LibreNMS\Util\Url::overlibLink($link, $graph, $overlib_content);
        echo '  </div>
            </div>';
        foreach ($total_percent as $values) {
            //Add a row with CPU desc, count and percent graph
            $percent_usage = ceil($values['usage'] / $values['count']);
            $percent_warn = $values['warn'] / $values['count'];
            $background = \LibreNMS\Util\Color::percentage($percent_usage, $percent_warn);

            echo '
              <div class="tw:grid tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-[#f5f5f5] tw:dark:hover:bg-dark-gray-300 tw:grid-cols-[2fr_1fr]">
                <div>' . \LibreNMS\Util\Url::overlibLink($link, 'x' . $values['count'] . ' ' . $values['descr'], $overlib_content) . '</div>
                <div>' . \LibreNMS\Util\Url::overlibLink($link, \LibreNMS\Util\Html::percentageBar(400, 10, $percent_usage, null, $percent_usage . '%', null, null, [
                'left' => $background['left'],
                'left_text' => null,
                'right' => $background['right'],
                'right_text' => null,
            ]), $overlib_content) . '</div>
              </div>';
        }
    }

    echo '</div>
        </div>';
}//end if
