<?php

echo "<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";

echo '<tr><th>Port</th><th>Traffic</th><th>Sync Speed</th><th>Attainable Speed</th><th>Attenuation</th><th>SNR Margin</th><th>Output Powers</th></tr>';
$i = '0';
$ports = dbFetchRows("select * from `ports` AS P, `ports_adsl` AS A WHERE P.device_id = ? AND A.port_id = P.port_id AND P.deleted = '0' ORDER BY `ifIndex` ASC", [$device['device_id']]);

foreach ($ports as $port) {
    include 'includes/html/print-interface-adsl.inc.php';
    $i++;
}

echo '</table></div>';
echo "<div style='min-height: 150px;'></div>";
