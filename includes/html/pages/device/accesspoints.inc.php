<?php

echo "<div style='margin: 0px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";

$i = '1';

if ($vars['ap'] > 0) { //We have a selected AP
    $aps = dbFetchRows("SELECT * FROM `access_points` WHERE `device_id` = ? AND `accesspoint_id` = ? AND `deleted` = '0' ORDER BY `name`,`radio_number` ASC", [$device['device_id'], $vars['ap']]);
} else {
    $aps = dbFetchRows("SELECT * FROM `access_points` WHERE `device_id` = ? AND `deleted` = '0' ORDER BY `name`,`radio_number` ASC", [$device['device_id']]);
}
echo "<div style='margin: 0px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";
foreach ($aps as $ap) {
    include 'includes/html/print-accesspoint.inc.php';
    $i++;
}

echo '</table></div>';

echo '</div>';
