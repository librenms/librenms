<?php

echo "<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";

echo '<tr><th>Port</th><th>Traffic</th><th>Sync Speed</th><th>Attainable Speed</th><th>Attenuation</th><th>SNR Margin</th><th>Output Powers</th></tr>';
$i = '0';

$ports = DeviceCache::getPrimary()->ports()->join('ports_adsl', 'ports.port_id', 'ports_adsl.port_id')
    ->where('ports.deleted', '0')
    ->orderby('ports.ifIndex', 'ASC')
    ->get();

foreach ($ports as $port) {
    include 'includes/html/print-interface-adsl.inc.php';
    $i++;
}

$ports = DeviceCache::getPrimary()->ports()->join('ports_vdsl', 'ports.port_id', '=', 'ports_vdsl.port_id')
    ->where('ports.deleted', '0')
    ->orderby('ports.ifIndex', 'ASC')
    ->get();

foreach ($ports as $port) {
    include 'includes/html/print-interface-vdsl.inc.php';
    $i++;
}
echo '</table></div>';
echo "<div style='min-height: 150px;'></div>";
