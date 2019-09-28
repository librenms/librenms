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
    switch ($component['system_state']) {
        case 'active':
        case 'activeSolo':
        case 'standby':
        case 'hotStandby':
            $state_label = 'label-success';
            break;
        case 'reset':
            $state_label = 'label-danger';
            break;
        case 'init':
            $state_label = 'label-warning';
            break;
        default:
            $state_label = 'label-default';
    }

    switch ($component['traffic_direction']) {
        case 'none':
            $direction_label = 'label-danger';
            break;
        case 'ingress':
        case 'egress':
            $direction_label = 'label-wanring';
            break;
        case 'both':
            $direction_label = 'label-success';
            break;
        default:
            $direction_label = 'label-default';
    }

    $text_descr = $component['name'];
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <div class='pull-left'>
                    <h2 style='margin: 0 5pt 0 0'><i class=\"fa fa-microchip fa-lg icon-theme\" aria-hidden=\"true\"></i></h2>
                </div>
                <h2 class='panel-title'><b>$text_descr</b>
                    <div class='pull-right'>
                        <span class='label {$state_label}'>State: {$component['system_state']}</span>
                        <span class='label {$direction_label}'>
                            Traffic direction: {$component['traffic_direction']}
                        </span>
                    </div>
                </h2>
                Last system load at <b>{$component['system_last_load']}</b>
            </div>";
    echo "<div class='panel-body'>";



    /*
     * QFP Utilization (Load)
     */

    if ($component['utilization'] < 50) {
        $util_label = 'label-success';
    }elseif ($component['utilization'] < 75) {
        $util_label = 'label-warning';
    } else {
        $util_label = 'label-danger';
    }

    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_util';
    $text_descr = 'QFP Utilizatoin';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    $text_descr
                    <div class='pull-right'><span class='label {$util_label}'>{$component['utilization']} %</span></div>
                </h3>
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
    $packets_label = 'label-default';
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_packets';
    $text_descr = 'QFP packets';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    $text_descr
                    <div class='pull-right'>
                        <span class='label {$packets_label}'>" . format_bi($component['packets']) . "pps</span>
                    </div>
                </h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";


    /*
     * QFP Throughput In/Out
     */
    $throughput_label = 'label-default';
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_throughput';
    $text_descr = 'QFP Throughput';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    $text_descr
                    <div class='pull-right'>
                        <span class='label {$throughput_label}'>" . format_bi($component['throughput']) . "bps</span>
                    </div>
                </h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";

    /*
     * QFP Average packet size
     */
    $psize_label = 'label-default';
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_avgpktsize';
    $text_descr = 'QFP Average packet size';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    $text_descr
                    <div class='pull-right'>
                        <span class='label {$psize_label}'>" . ceil($component['average_packet']) . " Bytes</span>
                    </div>
                </h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";

    /*
     * QFP Memory resources
     */
    $mem_prec = $component['memory_used']*100/$component['memory_total'];
    if ($mem_prec < 75) {
        $mem_label = 'label-success';
    }elseif ($mem_prec < 90) {
        $mem_label = 'label-warning';
    }else {
        $mem_label = 'label-danger';
    }
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_memory';
    $text_descr = 'QFP Memory';
    $label_text = sprintf("%sB / %sB", format_bi($component['memory_used']),format_bi($component['memory_total']));
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    $text_descr
                    <div class='pull-right'><span class='label {$mem_label}'>{$label_text}</span></div>
                </h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";
    echo "</div></div>";
}
