<?php

if (Rrd::checkRrdExists(Rrd::name($device['hostname'], Rrd::portName($port['port_id'], 'adsl')))) {
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

if (Rrd::checkRrdExists(Rrd::name($device['hostname'], Rrd::portName($port['port_id'], 'xdsl2LineStatusAttainableRate')))) {
    echo '<div class=graphhead>VDSL Current Line Speed</div>';
    $graph_type = 'port_vdsl_speed';

    include 'includes/html/print-interface-graphs.inc.php';

    echo '<div class=graphhead>VDSL Attainable Speed</div>';
    $graph_type = 'port_vdsl_attainable';

    include 'includes/html/print-interface-graphs.inc.php';

    echo '<div class=graphhead>VDSL Output Powers</div>';
    $graph_type = 'port_vdsl_power';

    include 'includes/html/print-interface-graphs.inc.php';
}
