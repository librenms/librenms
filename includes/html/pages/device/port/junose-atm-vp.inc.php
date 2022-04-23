<?php

$row = 0;

if ($_GET['optc']) {
    $graph_type = 'atmvp_' . $_GET['optc'];
}

if (! $graph_type) {
    $graph_type = 'atmvp_bits';
}

echo '<table cellspacing="0" cellpadding="5" border="0">';

foreach (dbFetchRows('SELECT * FROM juniAtmVp WHERE port_id = ?', [$interface['port_id']]) as $vp) {
    if (is_integer($row / 2)) {
        $row_colour = \LibreNMS\Config::get('list_colour.even');
    } else {
        $row_colour = \LibreNMS\Config::get('list_colour.odd');
    }

    echo '<tr bgcolor="' . $row_colour . '">';
    echo '<td><span class=list-bold>' . $row . '. VP' . $vp['vp_id'] . ' ' . $vp['vp_descr'] . '</span></td>';
    echo '</tr>';

    $graph_array['height'] = '100';
    $graph_array['width'] = '214';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $vp['juniAtmVp_id'];
    $graph_array['type'] = $graph_type;

    $periods = [
        'day',
        'week',
        'month',
        'year',
    ];

    echo '<tr bgcolor="' . $row_colour . '"><td>';

    foreach ($periods as $period) {
        $graph_array['from'] = $$period;
        $graph_array_zoom = $graph_array;
        $graph_array_zoom['height'] = '150';
        $graph_array_zoom['width'] = '400';
        echo \LibreNMS\Util\Url::overlibLink('#', \LibreNMS\Util\Url::lazyGraphTag($graph_array), \LibreNMS\Util\Url::graphTag($graph_array_zoom));
    }

    echo '</td></tr>';

    $row++;
}//end foreach

echo '</table>';
