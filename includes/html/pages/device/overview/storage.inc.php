<?php

use Illuminate\Support\Str;
use LibreNMS\Util\Number;

$graph_type = 'storage_usage';

$drives = dbFetchRows('SELECT * FROM `storage` WHERE device_id = ? ORDER BY `storage_descr` ASC', [$device['device_id']]);

if (count($drives)) {
    echo '
              <div class="overview-panel tw:mb-5">
                <div class="tw:px-4 tw:py-2.5 tw:bg-[#f5f5f5] tw:border-b tw:border-[#ddd] tw:text-[#333] tw:dark:bg-dark-gray-200 tw:dark:border-[#1c1e22] tw:dark:text-dark-white-200">';
    echo '<a href="device/device=' . $device['device_id'] . '/tab=health/metric=storage/">';
    echo '<i class="fa fa-database fa-lg icon-theme" aria-hidden="true"></i> <strong>Storage</strong></a>';
    echo '    </div>
            <div class="tw:flex tw:flex-col tw:bg-white tw:divide-y tw:divide-[#ddd] tw:dark:bg-dark-gray-400 tw:dark:divide-[#1c1e22]">';

    foreach ($drives as $drive) {
        $skipdrive = 0;

        if ($device['os'] == 'junos') {
            foreach (\App\Facades\LibrenmsConfig::get('ignore_junos_os_drives', []) as $jdrive) {
                if (preg_match($jdrive, (string) $drive['storage_descr'])) {
                    $skipdrive = 1;
                }
            }

            $drive['storage_descr'] = preg_replace('/.*mounted on: (.*)/', '\\1', (string) $drive['storage_descr']);
        }

        if ($device['os'] == 'freebsd') {
            foreach (\App\Facades\LibrenmsConfig::get('ignore_bsd_os_drives', []) as $jdrive) {
                if (preg_match($jdrive, (string) $drive['storage_descr'])) {
                    $skipdrive = 1;
                }
            }
        }

        if ($skipdrive) {
            continue;
        }

        $percent = round($drive['storage_perc']);
        $total = Number::formatBi($drive['storage_size']);
        $free = Number::formatBi($drive['storage_free']);
        $used = Number::formatBi($drive['storage_used']);
        $background = \LibreNMS\Util\Color::percentage($percent, $drive['storage_perc_warn']);

        $graph_array = [];
        $graph_array['height'] = '100';
        $graph_array['width'] = '210';
        $graph_array['to'] = \App\Facades\LibrenmsConfig::get('time.now');
        $graph_array['id'] = $drive['storage_id'];
        $graph_array['type'] = $graph_type;
        $graph_array['from'] = \App\Facades\LibrenmsConfig::get('time.day');
        $graph_array['legend'] = 'no';

        $link_array = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width'], $link_array['legend']);
        $link = \LibreNMS\Util\Url::generate($link_array);

        $drive['storage_descr'] = Str::limit($drive['storage_descr'], 50);

        $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . ' - ' . $drive['storage_descr']);

        $graph_array['width'] = 80;
        $graph_array['height'] = 20;
        $graph_array['bg'] = 'ffffff00';
        // the 00 at the end makes the area transparent.
        $minigraph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

        echo '<div class="tw:grid tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-[#f5f5f5] tw:dark:hover:bg-dark-gray-300 tw:grid-cols-3">
           <div>' . \LibreNMS\Util\Url::overlibLink($link, $drive['storage_descr'], $overlib_content) . '</div>
           <div>' . \LibreNMS\Util\Url::overlibLink($link, $minigraph, $overlib_content) . '</div>
           <div>' . \LibreNMS\Util\Url::overlibLink($link, \LibreNMS\Util\Html::percentageBar(400, 10, $percent, "$used / $total ($percent%)", $free, null, null, [
            'left' => $background['left'],
            'left_text' => null,
            'right' => $background['right'],
            'right_text' => null,
        ]), $overlib_content) . '
           </div>
         </div>';
    }//end foreach

    echo '</div>
        </div>';
}//end if

unset($drive_rows);
