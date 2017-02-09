<?php
///opt/librenms/rrd/adsl2.geordish.org/sensor-connected-clients-wifi-6.rrd
$rrd_filename = rrd_name($device['hostname'], 'sensor-connected-clients-wifi-' . $port['ifIndex']);

if (file_exists($rrd_filename)) {
    echo '<div class=graphhead>Number of connected clients</div>';
    $graph_type = 'port_wifi_clients';

    include 'includes/print-interface-graphs.inc.php';
}

/*
    echo '<div class=graphhead>AP Noise Floor</div>';
    $graph_type = 'port_routeros_noisefloor';

    include 'includes/print-interface-graphs.inc.php';
 
    echo '<div class=graphhead>Tx/Rx Rate</div>';
    $graph_type = 'port_routeros_rate';

    include 'includes/print-interface-graphs.inc.php';
 
    echo '<div class=graphhead>TxCCQ</div>';
    $graph_type = 'port_routeros_txccq';

    include 'includes/print-interface-graphs.inc.php';
}
 */
