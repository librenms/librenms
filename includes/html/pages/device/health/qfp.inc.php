<?php

$component = new \LibreNMS\Component();
$components = $component->getComponents($device['device_id'], array('type' => 'cisco-qfp'));
$components = $components[$device['device_id']];

foreach ($components as $component_id => $component) {

    $default_graph_array = array(
        'from' => \LibreNMS\Config::get('time.day'),
        'to' => \LibreNMS\Config::get('time.now'),
        'id' => $component_id,
        'page' => 'graphs'
    );

    /*
     * Main container for QFP
     */
    $text_descr = $component['name'];
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h2 class='panel-title'><b>$text_descr</b></h2>
            </div>";
    echo "<div class='panel-body'>";



    /*
     * QFP Utilization (Load)
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_util';
    $text_descr = 'QFP Utilizatoin';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";

    /*
     * Relative QFP utilization to packets processed
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_relativeutil';
    $text_descr = 'QFP Relative utilizatoin per kpps';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";

    /*
     * QFP Packets In/Out
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_packets';
    $text_descr = 'QFP packets';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";


    /*
     * QFP Throughput In/Out
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_throughput';
    $text_descr = 'QFP Throughput';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";

    /*
     * QFP Average packet size
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_avgpktsize';
    $text_descr = 'QFP Average packet size';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";

    /*
     * QFP Memory resources
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_memory';
    $text_descr = 'QFP Memory';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";
    echo "</div></div>";

}
