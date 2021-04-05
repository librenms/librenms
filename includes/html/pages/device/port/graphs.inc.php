<?php

if (Rrd::checkRrdExists(get_port_rrdfile_path($device['hostname'], $port['port_id']))) {
    $iid = $id;
    echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Traffic</h3>
            </div>';
    $graph_type = 'port_bits';

    echo '<div class="panel-body">';
    include 'includes/html/print-interface-graphs.inc.php';
    echo '</div></div>';

    echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Packets</h3>
            </div>';
    $graph_type = 'port_upkts';

    echo '<div class="panel-body">';
    include 'includes/html/print-interface-graphs.inc.php';
    echo '</div></div>';

    echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Non Unicast</h3>
            </div>';

    $graph_type = 'port_nupkts';
    echo '<div class="panel-body">';
    include 'includes/html/print-interface-graphs.inc.php';
    echo '</div></div>';

    echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Interface Errors</h3>
            </div>';

    $graph_type = 'port_errors';

    echo '<div class="panel-body">';
    include 'includes/html/print-interface-graphs.inc.php';
    echo '</div></div>';

    if (Rrd::checkRrdExists(get_port_rrdfile_path($device['hostname'], $port['port_id'], 'poe'))) {
        echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">PoE</h3>
            </div>';
        $graph_type = 'port_poe';

        echo '<div class="panel-body">';
        include 'includes/html/print-interface-graphs.inc.php';
        echo '</div></div>';
    }

    if (Rrd::checkRrdExists(get_port_rrdfile_path($device['hostname'], $port['port_id'], 'dot3'))) {
        echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Ethernet Errors</h3>
            </div>';
        $graph_type = 'port_etherlike';

        echo '<div class="panel-body">';
        include 'includes/html/print-interface-graphs.inc.php';
        echo '</div></div>';
    }

    /*
     *  CISCO-IF-EXTENSION MIB statistics
     *  Additional information about input and output errors as seen in `show interface` output.
     */
    if (Rrd::checkRrdExists(get_port_rrdfile_path($device['hostname'], $port['port_id'], 'cie'))) {
        echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Detailed interface errors</h3>
            </div>';
        $graph_type = 'port_cie';

        echo '<div class="panel-body">';
        include 'includes/html/print-interface-graphs.inc.php';
        echo '</div></div>';
    }
}
