<?php

$graph_type = 'storage_usage';

$row = 1;

foreach (dbFetchRows('SELECT * FROM `storage` WHERE device_id = ? ORDER BY storage_descr', array($device['device_id'])) as $drive) {
    if (is_integer($row / 2)) {
        $row_colour = $list_colour_a;
    }
    else {
        $row_colour = $list_colour_b;
    }

    $total = $drive['storage_size'];
    $used  = $drive['storage_used'];
    $free  = $drive['storage_free'];
    $perc  = round($drive['storage_perc'], 0);
    $used  = formatStorage($used);
    $total = formatStorage($total);
    $free  = formatStorage($free);
    $storage_descr = $drive['storage_descr'];

    $fs_url = 'graphs/id='.$drive['storage_id'].'/type=storage_usage/';

    $fs_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname'].' - '.$drive['storage_descr'];
    $fs_popup .= "</div><img src=\'graph.php?id=".$drive['storage_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now']."&amp;width=400&amp;height=125\'>";
    $fs_popup .= "', RIGHT, FGCOLOR, '#e5e5e5');\" onmouseout=\"return nd();\"";


    $graph_array['id']   = $drive['storage_id'];
    $graph_array['type'] = $graph_type;

    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$storage_descr <div class='pull-right'>$used/$total - $perc% used</div></h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/print-graphrow.inc.php';
    echo "</div></div>";

    $row++;
}//end foreach
