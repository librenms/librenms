<?php

use LibreNMS\Util\Number;

$graph_type = 'storage_usage';

$drives = dbFetchRows('SELECT * FROM `storage` WHERE device_id = ? ORDER BY `storage_descr` ASC', [$device['device_id']]);

if (count($drives)) {
    echo '
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default panel-condensed">
                <div class="panel-heading">';
    echo '<a href="device/device=' . $device['device_id'] . '/tab=health/metric=storage/">';
    echo '<i class="fa fa-database fa-lg icon-theme" aria-hidden="true"></i> <strong>Storage</strong></a>';
    echo '    </div>
            <table class="table table-hover table-condensed table-striped">';

    foreach ($drives as $drive) {
        $skipdrive = 0;

        if ($device['os'] == 'junos') {
            foreach (\LibreNMS\Config::get('ignore_junos_os_drives') as $jdrive) {
                if (preg_match($jdrive, $drive['storage_descr'])) {
                    $skipdrive = 1;
                }
            }

            $drive['storage_descr'] = preg_replace('/.*mounted on: (.*)/', '\\1', $drive['storage_descr']);
        }

        if ($device['os'] == 'freebsd') {
            foreach (\LibreNMS\Config::get('ignore_bsd_os_drives') as $jdrive) {
                if (preg_match($jdrive, $drive['storage_descr'])) {
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
        $background = \LibreNMS\Util\Colors::percentage($percent, $drive['storage_perc_warn']);

        $graph_array = [];
        $graph_array['height'] = '100';
        $graph_array['width'] = '210';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $drive['storage_id'];
        $graph_array['type'] = $graph_type;
        $graph_array['from'] = \LibreNMS\Config::get('time.day');
        $graph_array['legend'] = 'no';

        $link_array = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width'], $link_array['legend']);
        $link = \LibreNMS\Util\Url::generate($link_array);

        $drive['storage_descr'] = \LibreNMS\Util\StringHelpers::shortenText($drive['storage_descr'], 50);

        $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . ' - ' . $drive['storage_descr']);

        $graph_array['width'] = 80;
        $graph_array['height'] = 20;
        $graph_array['bg'] = 'ffffff00';
        // the 00 at the end makes the area transparent.
        $minigraph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

        echo '<tr>
           <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, $drive['storage_descr'], $overlib_content) . '</td>
           <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, $minigraph, $overlib_content) . '</td>
           <td class="col-md-4">' . \LibreNMS\Util\Url::overlibLink($link, print_percentage_bar(200, 20, $percent, null, 'ffffff', $background['left'], $percent . '%', 'ffffff', $background['right']), $overlib_content) . '
           </a></td>
         </tr>';
    }//end foreach

    echo '</table>
        </div>
        </div>
        </div>';
}//end if

unset($drive_rows);
