<?php

$entries = dbFetchRows("SELECT *,DATE_FORMAT(datetime, '".$config['dateformat']['mysql']['compact']."') as humandate  FROM `eventlog` WHERE `host` = ? AND `type` = 'interface' AND `reference` = '".$port['port_id']."' ORDER BY `datetime` DESC LIMIT 0,250", array($device['device_id']));
echo '<table class="table table-condensed">';
echo '<th>Timestamp</th><th>Port</th><th>Event</th>';

foreach ($entries as $entry) {
    include 'includes/print-event.inc.php';
}

echo '</table>';

$pagetitle[] = 'Events';
