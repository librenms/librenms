<?php

use App\Models\Pseudowire;
use LibreNMS\Util\Url;

$pagetitle[] = 'Pseudowires';

$vars['view'] ??= 'detail';

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

$linkdone = [];
$bg = '';

$pseudowires = Pseudowire::query()
    ->whereHas('port')
    ->whereHas('device')
    ->with(['port', 'port.device', 'device'])
    ->get()
    ->sort(fn ($a, $b) => [$a->device?->hostname, $a->port?->ifDescr] <=> [$b->device?->hostname, $b->port?->ifDescr]);

foreach ($pseudowires as $pw_a) {
    if (in_array($pw_a->device_id . $pw_a->port_id, $linkdone)) {
        continue;
    }

    $pw_b = Pseudowire::query()
        ->whereHas('port')
        ->where('device_id', $pw_a->peer_device_id)
        ->where('cpwVcID', $pw_a->cpwVcID)
        ->with(['port', 'port.device', 'device'])
        ->first();

    if (! $pw_b || ! port_permitted($pw_a->port_id) || ! port_permitted($pw_b->port_id)) {
        continue;
    }

    if ($bg == 'ffffff') {
        $bg = 'e5e5e5';
    } else {
        $bg = 'ffffff';
    }

    echo "<tr style=\"background-color: #$bg;\"><td rowspan=2 style='font-size:18px; padding:4px;'>" . $pw_a->cpwVcID . '</td><td>' . generate_device_link($pw_a->device) . '</td><td>' . generate_port_link($pw_a->port) . "</td>
                                                                                          <td rowspan=2> <i class='fa fa-arrows-alt fa-lg icon-theme' aria-hidden='true'></i> </td>
                                                                                          <td>" . generate_device_link($pw_b->device) . '</td><td>' . generate_port_link($pw_b->port) . '</td></tr>';
    echo "<tr style=\"background-color: #$bg;\"><td colspan=2>" . e($pw_a->port?->ifAlias) . '</td><td colspan=2>' . e($pw_b->port?->ifAlias) . '</td></tr>';

    if ($vars['view'] == 'minigraphs') {
        echo "<tr style=\"background-color: #$bg;\"><td></td><td colspan=2>";

        foreach (['bits', 'upkts', 'errors'] as $graph_type) {
            echo generate_port_link($pw_a->port, Url::graphTag([
                'type' => 'port_' . $graph_type,
                'id' => $pw_a->port_id,
                'width' => 150,
                'height' => 30,
                'from' => '-1d',
                'bg' => $bg,
            ]));
        }

        echo '</td><td></td><td colspan=2>';

        foreach (['bits', 'upkts', 'errors'] as $graph_type) {
            echo generate_port_link($pw_b->port, Url::graphTag([
                'type' => 'port_' . $graph_type,
                'id' => $pw_b->port_id,
                'width' => 150,
                'height' => 30,
                'from' => '-1d',
                'bg' => $bg,
            ]));
        }

        echo '</td></tr>';
    }//end if

    $linkdone[] = $pw_b->device_id . $pw_b->port_id;
}//end foreach

echo '</table>';
