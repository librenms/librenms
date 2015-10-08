<?php

echo "<div style='margin: 0px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";

$i = '1';

$ap = dbFetchRow("SELECT * FROM `access_points` WHERE `device_id` = ? AND `accesspoint_id` = ? AND `deleted` = '0' ORDER BY `name`,`radio_number` ASC", array($device['device_id'], $vars['ap']));

echo "<div class=ifcell style='margin: 0px;'><table width=100% cellpadding=10 cellspacing=0>";

require 'includes/print-accesspoint.inc.php';

echo '</table></div>';
