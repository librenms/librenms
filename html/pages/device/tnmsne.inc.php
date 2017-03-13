<?php
$pagetitle[] = 'Coriant NE Devices';
echo '<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table table-condensed"><tr class="tablehead"><th>Name</th><th>Location</th><th>Type</th><th>Operation Mode</th><th>Alarm</th><th>State</th></tr>';
$i = '1';

foreach (dbFetchRows('SELECT `neName`,`neLocation`,`neType`,`neOpMode`,`neAlarm`,`neOpState` FROM `tnmsneinfo` WHERE `device_id` = ? ORDER BY `neID`,`neName`,`neLocation`', array($device['device_id'])) as $tnmsne) {
    include 'includes/print-tnmsne.inc.php';
    $i++;
}

echo '</table>';
