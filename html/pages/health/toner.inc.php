<?php

$graph_type = 'toner_usage';

echo "<div style='padding: 5px;'>
        <table width=100% cellspacing=0 cellpadding=6 class='sortable'>";

echo '<tr class=tablehead>
        <th width=280>Device</th>
        <th>Toner</th>
        <th width=100></th>
        <th width=280>Usage</th>
        <th width=50>Used</th>
      </tr>';

foreach (dbFetchRows('SELECT * FROM `toner` AS S, `devices` AS D WHERE S.device_id = D.device_id ORDER BY D.hostname, S.toner_descr') as $toner) {
    if (device_permitted($toner['device_id'])) {
        $total = $toner['toner_capacity'];
        $perc  = $toner['toner_current'];

        $graph_array['type']        = $graph_type;
        $graph_array['id']          = $toner['toner_id'];
        $graph_array['from']        = $config['time']['day'];
        $graph_array['to']          = $config['time']['now'];
        $graph_array['height']      = '20';
        $graph_array['width']       = '80';
        $graph_array_zoom           = $graph_array;
        $graph_array_zoom['height'] = '150';
        $graph_array_zoom['width']  = '400';
        $link       = 'graphs/id='.$graph_array['id'].'/type='.$graph_array['type'].'/from='.$graph_array['from'].'/to='.$graph_array['to'].'/';
        $mini_graph = overlib_link($link, generate_lazy_graph_tag($graph_array), generate_graph_tag($graph_array_zoom), null);

        $background = get_percentage_colours(100 - $perc);

        echo "<tr class='health'><td>".generate_device_link($toner).'</td><td class=tablehead>'.$toner['toner_descr']."</td>
         <td>$mini_graph</td>
         <td>
          <a href='#' $store_popup>".print_percentage_bar(400, 20, $perc, "$perc%", 'ffffff', $background['left'], $free, 'ffffff', $background['right'])."</a>
          </td><td>$perc".'%</td></tr>';

        if ($vars['view'] == 'graphs') {
            echo "<tr></tr><tr class='health'><td colspan=5>";

            $graph_array['height'] = '100';
            $graph_array['width']  = '216';
            $graph_array['to']     = $config['time']['now'];
            $graph_array['id']     = $toner['toner_id'];
            $graph_array['type']   = $graph_type;

            include 'includes/print-graphrow.inc.php';

            echo '</td></tr>';
        }
    }
}

echo '</table></div>';
