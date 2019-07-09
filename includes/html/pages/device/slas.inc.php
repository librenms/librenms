<?php

print_optionbar_start();

$slas = dbFetchRows('SELECT * FROM `slas` WHERE `device_id` = ? AND `deleted` = 0 ORDER BY `sla_nr`', array($device['device_id']));

// Collect types
$sla_types = array('all' => 'All');
foreach ($slas as $sla) {
    $sla_type = $sla['rtt_type'];

    $sla_types[$sla_type] = !in_array($sla_type, $sla_types) ? \LibreNMS\Config::get("sla_type_labels.$sla_type", 'Unknown') : ucfirst($sla_type);
}
asort($sla_types);

$status_options = array(
    'all'   => 'All',
    'up'    => 'Up',
    'down'  => 'Down',
);

echo "<span style='font-weight: bold;'>SLA</span> &#187; ";

// SLA Types, on the left.
$sep = '';
foreach ($sla_types as $sla_type => $text) {
    if (!$vars['view']) {
        $vars['view'] = $sla_type;
    }

    echo $sep;
    if ($vars['view'] == $sla_type) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $vars, array('view' => $sla_type));
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

    echo generate_link($text, $vars, array('opstatus' => $option));
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

    $opstatus = ($sla['opstatus'] === '0') ? 'up' : 'down';
    d_echo("<br>Opstatus :: var: ".$vars['opstatus'].", db: ".$sla['opstatus'].", name: ".$opstatus."<br>");
    if ($vars['opstatus'] != 'all' && $vars['opstatus'] != $opstatus) {
        continue;
    }

    $name = 'SLA #'.$sla['sla_nr'].' - '.$sla_types[$sla['rtt_type']];
    if ($sla['tag']) {
        $name .= ': '.$sla['tag'];
    }

    if ($sla['owner']) {
        $name .= ' (Owner: '.$sla['owner'].')';
    }

    // These Types have more graphs. Display a sub-page
    if (($sla['rtt_type'] == 'jitter') || ($sla['rtt_type'] == 'icmpjitter')) {
        $name = '<a href="'.generate_url($vars, array('tab' => "sla", 'id' => $sla['sla_id'])).'">'.$name.'</a>';
    } else {
        $name = htmlentities($name);
    }

    // If we have an error highlight the row.
    if ($sla['opstatus'] == 2) {
        $danger = "panel-danger";
    } else {
        $danger = '';
    }

    $graph_array = array();
    $graph_array['device']  = $device['device_id'];
    $graph_array['height']  = '100';
    $graph_array['width']   = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['type']    = 'device_sla';
    $graph_array['id']      = $sla['sla_id'];
    echo '<div class="panel panel-default '.$danger.'">
    <div class="panel-heading">
        <h3 class="panel-title">'.$name.'</h3>
    </div>
    <div class="panel-body">';
    echo "<div class='row'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

$pagetitle[] = 'SLAs';
