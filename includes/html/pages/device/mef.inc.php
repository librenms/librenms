<?php

$pagetitle[] = 'Metro Ethernet';
echo '<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table table-condensed"><tr class="tablehead"><th>Link</th><th>Type</th><th>MTU</th><th>Admin State</th><th>Row State</th></tr>';
$i = '1';

foreach (dbFetchRows('SELECT `mefIdent`,`mefType`,`mefMTU`,`mefAdmState`,`mefRowState` FROM `mefinfo` WHERE `device_id` = ? ORDER BY `mefID`', [$device['device_id']]) as $mef) {
    include 'includes/html/print-mef.inc.php';
    $i++;
}

echo '</table>';
