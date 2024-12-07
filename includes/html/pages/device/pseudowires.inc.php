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

echo '<table class="table">';
echo '<tr><th>PW ID</th><th>Local PW Name</th><th>Local Port</th><td></th><th>Remote Device/PW Name</th><th>Remote Port</th></tr>';

$linkdone = [];
$bg = '';

foreach (dbFetchRows('SELECT * FROM pseudowires AS P, ports AS I WHERE P.port_id = I.port_id AND I.device_id = ? ORDER BY I.ifDescr', [$device['device_id']]) as $pw_a) {
    $pw_a = cleanPort($pw_a);
    if (in_array($pw_a['device_id'] . $pw_a['port_id'], $linkdone)) {
        continue;
    }
    if (! port_permitted($pw_a['port_id'])) {
        continue;
    }

    // if the remote device is valid, resolve their pw & port details
    if ($pw_a['peer_device_id'] != 0) {
        $pw_b = dbFetchRow(
            'SELECT * from `devices` AS D, `ports` AS I, `pseudowires` AS P WHERE D.device_id = ? AND D.device_id = I.device_id
            AND P.cpwVcID = ? AND P.port_id = I.port_id',
            [$pw_a['peer_device_id'], $pw_a['cpwVcID']]
        );
        $pw_b = cleanPort($pw_b);
        if (! port_permitted($pw_b['port_id'])) {
            continue;
        }
    } else {
        unset($pw_b);
    }

    if ($bg == '255,255,255') {
        $bg = '238, 238, 238';
    } else {
        $bg = '255,255,255';
    }

    echo '<tr style="background-color: rgb(' . $bg . ')">
            <td style="font-size:18px; padding:4px;vertical-align: middle;">' . $pw_a['cpwVcID'] . '</td>
            <td>' . $pw_a['pw_descr'] . '<br/><span class="box-desc">' . $pw_a['pw_type'] . ' ' . $pw_a['pw_psntype'] . '</span></td>
            <td>' . generate_port_link($pw_a) . ' <i class="fa fa-arrow-' . $pw_a['ifOperStatus'] . ' report-' . $pw_a['ifOperStatus'] . '" aria-hidden="true"></i><br/><span class="interface-desc">' . $pw_a['ifAlias'] . '</span>';
    if ($pw_a['pw_local_mtu'] != 0) {
        echo '<br/><span class="box-desc">MTU ' . $pw_a['ifMtu'] . '</span>';
        echo '<br/><span class="box-desc">PW MTU ' . $pw_a['pw_local_mtu'] . '</span>';
    } else {
        echo '<br/><span class="box-desc">MTU ' . $pw_a['ifMtu'] . '</span>';
    }
    echo '</td>
            <td style="vertical-align: middle;"> <i class="fa fa-times" aria-hidden="true" style="font-size:2em;"></i></span> </td>';

    //Only if b-endpoint was found
    if ($pw_b) {
        echo '<td>' . generate_device_link($pw_b) . '<br/><span class="box-desc"> ' . $pw_b['pw_descr'] . '</span></td>
                <td>' . generate_port_link($pw_b) . ' <i class="fa fa-arrow-' . $pw_b['ifOperStatus'] . ' report-' . $pw_b['ifOperStatus'] . '" aria-hidden="true"></i><br/><span class="interface-desc">' . $pw_b['ifAlias'] . '</span>';

        if ($pw_b['pw_local_mtu'] != 0) {
            echo '<br/><span class="box-desc">MTU ' . $pw_b['ifMtu'] . '</span>';
            echo '<br/><span class="box-desc">PW MTU ' . $pw_b['pw_local_mtu'] . '</span>';
        } else {
            echo '<br/><span class="box-desc">MTU ' . $pw_b['ifMtu'] . '</span>';
        }
    } else {
        echo '<td style="font-style: italic; vertical-align: middle;">unresolved remote device</td><td></td>';
    }

    echo '</tr>';

    if ($vars['view'] == 'minigraphs') {
        echo '<tr style="background-color: rgb(' . $bg . ')"><td></td><td colspan=2>';

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

echo '</table>';
