<?php

$pagetitle[] = 'Pseudowires';

if (! isset($vars['view'])) {
    $vars['view'] = 'detail';
}

$link_array = ['page' => 'pseudowires'];

print_optionbar_start();

echo '<span style="font-weight: bold;">Pseudowires</span> &#187; ';

if ($vars['view'] == 'detail') {
    echo '<span class="pagemenu-selected">';
}

echo generate_link('Details', $link_array, ['view' => 'detail']);
if ($vars['view'] == 'detail') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'minigraphs') {
    echo '<span class="pagemenu-selected">';
}

echo generate_link('Mini Graphs', $link_array, ['view' => 'minigraphs']);
if ($vars['view'] == 'minigraphs') {
    echo '</span>';
}

print_optionbar_end();

echo '<table cellpadding=5 cellspacing=0 class=devicetable width=100%>';

foreach (dbFetchRows('SELECT * FROM pseudowires AS P, ports AS I WHERE P.port_id = I.port_id AND I.device_id = ? ORDER BY I.ifDescr', [$device['device_id']]) as $pw_a) {
    $pw_a = cleanPort($pw_a);
    $i = 0;
    while ($i < count($linkdone)) {
        $thislink = $pw_a['device_id'] . $pw_a['port_id'];
        if ($linkdone[$i] == $thislink) {
            $skip = 'yes';
        }

        $i++;
    }

    $pw_b = dbFetchRow(
        'SELECT * from `devices` AS D, `ports` AS I, `pseudowires` AS P WHERE D.device_id = ? AND D.device_id = I.device_id
        AND P.cpwVcID = ? AND P.port_id = I.port_id',
        [$pw_a['peer_device_id'], $pw_a['cpwVcID']]
    );
    $pw_b = cleanPort($pw_b);

    if (! port_permitted($pw_a['port_id'])) {
        $skip = 'yes';
    }

    if (! port_permitted($pw_b['port_id'])) {
        $skip = 'yes';
    }

    if ($skip) {
        unset($skip);
    } else {
        if ($bg == 'ffffff') {
            $bg = 'e5e5e5';
        } else {
            $bg = 'ffffff';
        }

        echo "<tr style=\"background-color: #$bg;\"><td rowspan=2 style='font-size:18px; padding:4px;'>" . $pw_a['cpwVcID'] . '</td><td>' . generate_port_link($pw_a) . "</td>
            <td rowspan=2> <i class='fa fa-arrows-alt fa-lg icon-theme' aria-hidden='true'></i> </td>
            <td>" . generate_device_link($pw_b) . '</td><td>' . generate_port_link($pw_b) . '</td></tr>';
        echo "<tr style=\"background-color: #$bg;\"><td colspan=2>" . $pw_a['ifAlias'] . '</td><td>' . $pw_b['ifAlias'] . '</td></tr>';

        if ($vars['view'] == 'minigraphs') {
            echo "<tr style=\"background-color: #$bg;\"><td></td><td colspan=2>";

            if ($pw_a) {
                $pw_a['width'] = '150';
                $pw_a['height'] = '30';
                $pw_a['from'] = \LibreNMS\Config::get('time.day');
                $pw_a['to'] = \LibreNMS\Config::get('time.now');
                $pw_a['bg'] = $bg;
                $types = [
                    'bits',
                    'upkts',
                    'errors',
                ];
                foreach ($types as $graph_type) {
                    $pw_a['graph_type'] = 'port_' . $graph_type;
                    print_port_thumbnail($pw_a);
                }
            }

            echo '</td><td></td><td colspan=2>';

            if ($pw_b) {
                $pw_b['width'] = '150';
                $pw_b['height'] = '30';
                $pw_b['from'] = \LibreNMS\Config::get('time.day');
                $pw_b['to'] = \LibreNMS\Config::get('time.now');
                $pw_b['bg'] = $bg;
                $types = ['bits', 'upkts', 'errors'];
                foreach ($types as $graph_type) {
                    $pw_b['graph_type'] = 'port_' . $graph_type;
                    print_port_thumbnail($pw_b);
                }
            }

            echo '</td></tr>';
        }

        $linkdone[] = $pw_b['device_id'] . $pw_b['port_id'];
    }
}

echo '</table>';
