<?php

echo '
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
          <div class="panel-heading">';
echo '<a href="device/device=' . $device['device_id'] . '/tab=health/metric=mempool/">';
echo '<i class="fa fa-arrows fa-lg icon-theme" aria-hidden="true"></i> <strong>Catalyst 6k Crossbar</strong></a>';
echo '          </div>
    <table class="table table-hover table-condensed table-striped">';

if (! isset($entity_state)) {
    $entity_state = get_dev_entity_state($device['device_id']);
}
foreach ($entity_state['group']['c6kxbar'] as $index => $entry) {
    // FIXME i'm not sure if this is the correct way to decide what entphysical index it is. slotnum+1? :>
    $entity = dbFetchRow('SELECT * FROM entPhysical WHERE device_id = ? AND entPhysicalIndex = ?', [$device['device_id'], $index + 1]);

    echo "<tr>
        <td colspan='5'><strong>" . $entity['entPhysicalName'] . "</strong></td>
        <td colspan='2'>";

    switch ($entry['']['cc6kxbarModuleModeSwitchingMode']) {
        case 'busmode':
            // echo '<a title="Modules in this mode don't use fabric. Backplane is used for both lookup and data forwarding.">Bus</a>';
            break;

        case 'crossbarmode':
            echo '<a title="Modules in this mode use backplane for forwarding decision and fabric for data forwarding.">Crossbar</a>';
            break;

        case 'dcefmode':
            echo '<a title="Modules in this mode use fabric for data forwarding and local forwarding is enabled.">DCEF</a>';
            break;

        default:
            echo $entry['']['cc6kxbarModuleModeSwitchingMode'];
    }

    echo '</td>
        </tr>';

    foreach ($entity_state['group']['c6kxbar'][$index] as $subindex => $fabric) {
        if (is_numeric($subindex)) {
            if ($fabric['cc6kxbarModuleChannelFabStatus'] == 'ok') {
                $fabric['mode_class'] = 'green';
            } else {
                $fabric['mode_class'] = 'red';
            }

            $percent_in = $fabric['cc6kxbarStatisticsInUtil'];
            $background_in = \LibreNMS\Util\Colors::percentage($percent_in, null);

            $percent_out = $fabric['cc6kxbarStatisticsOutUtil'];
            $background_out = \LibreNMS\Util\Colors::percentage($percent_out, null);

            $graph_array = [];
            $graph_array['height'] = '100';
            $graph_array['width'] = '210';
            $graph_array['to'] = \LibreNMS\Config::get('time.now');
            $graph_array['device'] = $device['device_id'];
            $graph_array['mod'] = $index;
            $graph_array['chan'] = $subindex;
            $graph_array['type'] = 'c6kxbar_util';
            $graph_array['from'] = \LibreNMS\Config::get('time.day');
            $graph_array['legend'] = 'no';

            $link_array = $graph_array;
            $link_array['page'] = 'graphs';
            unset($link_array['height'], $link_array['width'], $link_array['legend']);
            $link = \LibreNMS\Util\Url::generate($link_array);

            $text_descr = $entity['entPhysicalName'] . ' - Fabric ' . $subindex;

            $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . ' - ' . $text_descr);

            $graph_array['width'] = 80;
            $graph_array['height'] = 20;
            $graph_array['bg'] = 'ffffff00';
            // the 00 at the end makes the area transparent.
            $minigraph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

            echo '<tr>
                <td></td>
                <td><strong>Fabric ' . $subindex . "</strong></td>
                <td><span style='font-weight: bold;' class=" . $fabric['mode_class'] . '>' . $fabric['cc6kxbarModuleChannelFabStatus'] . '</span></td>
                <td>' . \LibreNMS\Util\Number::formatSi(($fabric['cc6kxbarModuleChannelSpeed'] * 1000000), 2, 3, 'bps') . '</td>
                <td>' . \LibreNMS\Util\Url::overlibLink($link, $minigraph, $overlib_content) . '</td>
                <td>' . print_percentage_bar(125, 20, $percent_in, 'Ingress', 'ffffff', $background_in['left'], $percent_in . '%', 'ffffff', $background_in['right']) . '</td>
                <td>' . print_percentage_bar(125, 20, $percent_out, 'Egress', 'ffffff', $background_out['left'], $percent_out . '%', 'ffffff', $background_out['right']) . '</td>
                </tr>';
        }//end if
    }//end foreach
}//end foreach

echo '        </table>';
echo '      </div>';
echo '    </div>';
echo '  </div>';
