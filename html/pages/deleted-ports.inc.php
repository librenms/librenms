<?php

$pagetitle[] = 'Deleted ports';

if ($vars['purge'] == 'all') {
    foreach (dbFetchRows("SELECT * FROM `ports` AS P, `devices` as D WHERE P.`deleted` = '1' AND D.device_id = P.device_id") as $interface) {
        if (port_permitted($interface['port_id'], $interface['device_id'])) {
            delete_port($interface['port_id']);
            echo '<div class=infobox>Deleted '.generate_device_link($interface).' - '.generate_port_link($interface).'</div>';
        }
    }
}
else if ($vars['purge']) {
    $interface = dbFetchRow('SELECT * from `ports` AS P, `devices` AS D WHERE `port_id` = ? AND D.device_id = P.device_id', array($vars['purge']));
    if (port_permitted($interface['port_id'], $interface['device_id'])) {
        delete_port($interface['port_id']);
    }

    echo '<div class=infobox>Deleted '.generate_device_link($interface).' - '.generate_port_link($interface).'</div>';
}

echo '<table cellpadding=5 cellspacing=0 border=0 width=100%>';
echo "<tr><td></td><td></td><td></td><td><a href='deleted-ports/purge=all/'><img src='images/16/cross.png' align=absmiddle></img> Purge All</a></td></tr>";

foreach (dbFetchRows("SELECT * FROM `ports` AS P, `devices` as D WHERE P.`deleted` = '1' AND D.device_id = P.device_id",array(),true) as $interface) {
    $interface = ifLabel($interface, $interface);
    if (port_permitted($interface['port_id'], $interface['device_id'])) {
        echo '<tr class=list>';
        echo '<td width=250>'.generate_device_link($interface).'</td>';
        echo '<td width=250>'.generate_port_link($interface).'</td>';
        echo '<td></td>';
        echo "<td width=100><a href='deleted-ports/purge=".$interface['port_id']."/'><img src='images/16/cross.png' align=absmiddle></img> Purge</a></td>";
    }
}

echo '</table>';
