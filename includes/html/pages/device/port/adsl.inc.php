<?php

if (file_exists(get_port_rrdfile_path($device['hostname'], $port['port_id'], 'adsl'))) {
    $iid = $id;
    echo '<div class=graphhead>ADSL Current Line Speed</div>';
    $graph_type = 'port_adsl_speed';

    include 'includes/html/print-interface-graphs.inc.php';

    echo '<div class=graphhead>ADSL Attainable Speed</div>';
    $graph_type = 'port_adsl_attainable';

    include 'includes/html/print-interface-graphs.inc.php';

    echo '<div class=graphhead>ADSL Line Attenuation</div>';
    $graph_type = 'port_adsl_attenuation';

    include 'includes/html/print-interface-graphs.inc.php';

    echo '<div class=graphhead>ADSL Line SNR Margin</div>';
    $graph_type = 'port_adsl_snr';

    include 'includes/html/print-interface-graphs.inc.php';

    echo '<div class=graphhead>ADSL Output Powers</div>';
    $graph_type = 'port_adsl_power';

    include 'includes/html/print-interface-graphs.inc.php';
}
