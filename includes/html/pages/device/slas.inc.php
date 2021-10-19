<?php

if ($vars['id']) {
    $sla = dbFetchRow('SELECT `tag`, `sla_nr`,`rtt_type` FROM `slas` WHERE `sla_id` = ?', [$vars['id']]);
    $name = 'SLA #' . $sla['sla_nr'] . ' - ' . trans_fb("modules.slas.{$sla['rtt_type']}", ucfirst($sla['rtt_type']));
    if ($sla['tag']) {
        $name .= ': ' . $sla['tag'];
    }

    if ($sla['owner']) {
        $name .= ' (Owner: ' . $sla['owner'] . ')';
    }

    if ($sla['opstatus'] == 2) {
        $danger = 'panel-danger';
    } else {
        $danger = '';
    }

    echo "<div class=\"well well-sm\"><h3 class=\"panel-title\">$name</h3></div>";

    echo '<div class="panel panel-default ' . $danger . '">';

    // All SLA's support the RTT metric
    include 'sla/rtt.inc.php';

    // Load the per-type SLA metrics
    $rtt_type = basename($sla['rtt_type']);
    if (file_exists("includes/html/pages/device/sla/$rtt_type.inc.php")) {
        include "includes/html/pages/device/sla/$rtt_type.inc.php";
    }

    echo '</div>';
} else {
    print_optionbar_start();

    $slas = dbFetchRows('SELECT * FROM `slas` WHERE `device_id` = ? AND `deleted` = 0 ORDER BY `sla_nr`', [$device['device_id']]);

    // Collect types
    $sla_types = ['all' => 'All'];
    foreach ($slas as $sla) {
        $sla_type = $sla['rtt_type'];
        $sla_types[$sla_type] = trans_fb("modules.slas.{$sla_type}", ucfirst($sla_type));
    }
    asort($sla_types);

    $status_options = [
        'all' => 'All',
        'up' => 'Up',
        'down' => 'Down',
    ];

    echo "<span style='font-weight: bold;'>SLA</span> &#187; ";

    // SLA Types, on the left.
    $sep = '';
    foreach ($sla_types as $sla_type => $text) {
        if (! $vars['view']) {
            $vars['view'] = $sla_type;
        }

        echo $sep;
        if ($vars['view'] == $sla_type) {
            echo "<span class='pagemenu-selected'>";
        }

        echo generate_link($text, $vars, ['view' => $sla_type]);
        if ($vars['view'] == $sla_type) {
            echo '</span>';
        }

        $sep = ' | ';
    }
    unset($sep);

    // The status option - on the right
    echo '<div class="pull-right">';
    echo "<span style='font-weight: bold;'>Status</span> &#187; ";
    $sep = '';
    foreach ($status_options as $option => $text) {
        if (empty($vars['opstatus'])) {
            $vars['opstatus'] = $option;
        }
        echo $sep;
        if ($vars['opstatus'] == $option) {
            echo "<span class='pagemenu-selected'>";
        }

        echo generate_link($text, $vars, ['opstatus' => $option]);
        if ($vars['opstatus'] == $option) {
            echo '</span>';
        }
        $sep = ' | ';
    }
    unset($sep);

    print_optionbar_end();

    foreach ($slas as $sla) {
        if ($vars['view'] != 'all' && $vars['view'] != $sla['rtt_type']) {
            continue;
        }

        $opstatus = ($sla['opstatus'] === 0) ? 'up' : 'down';
        d_echo('<br>Opstatus :: var: ' . $vars['opstatus'] . ', db: ' . $sla['opstatus'] . ', name: ' . $opstatus . '<br>');
        if ($vars['opstatus'] != 'all' && $vars['opstatus'] != $opstatus) {
            continue;
        }

        $name = 'SLA #' . $sla['sla_nr'] . ' - ' . $sla_types[$sla['rtt_type']];
        if ($sla['tag']) {
            $name .= ': ' . $sla['tag'];
        }

        if ($sla['owner']) {
            $name .= ' (Owner: ' . $sla['owner'] . ')';
        }

        // These Types have more graphs. Display a sub-page
        if (($sla['rtt_type'] == 'jitter') || ($sla['rtt_type'] == 'icmpjitter') || ($sla['rtt_type'] == 'IcmpEcho') || ($sla['rtt_type'] == 'IcmpTimeStamp') || ($sla['rtt_type'] == 'icmpAppl')) {
            $name = '<a href="' . \LibreNMS\Util\Url::generate($vars, ['tab' => 'slas', 'id' => $sla['sla_id']]) . '">' . $name . '</a>';
        } else {
            $name = htmlentities($name);
        }

        // If we have an error highlight the row.
        if ($sla['opstatus'] == 2) {
            $danger = 'panel-danger';
        } else {
            $danger = '';
        }

        $graph_array = [];
        $graph_array['device'] = $device['device_id'];
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['type'] = 'device_sla';
        $graph_array['id'] = $sla['sla_id'];
        echo '<div class="panel panel-default ' . $danger . '">
    <div class="panel-heading">
        <h3 class="panel-title">' . $name . '</h3>
    </div>
    <div class="panel-body">';
        echo "<div class='row'>";
        include 'includes/html/print-graphrow.inc.php';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

$pagetitle[] = 'SLAs';
