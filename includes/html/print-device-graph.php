<?php

if (empty($graph_array['type'])) {
    $graph_array['type'] = $graph_type;
}
if (empty($graph_array['device'])) {
    $graph_array['device'] = $device['device_id'];
}
// FIXME not css alternating yet
if (! is_integer($g_i / 2)) {
    $row_colour = \LibreNMS\Config::get('list_colour.even');
} else {
    $row_colour = \LibreNMS\Config::get('list_colour.odd');
}
echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $graph_title . '</h3>
    </div>
    <div class="panel-body">
';
echo "<div class='row'>";
require 'includes/html/print-graphrow.inc.php';
echo '</div>';
echo '</div>';
echo '</div>';
$g_i++;
