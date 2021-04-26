<?php
/**
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 LibreNMS
 * @author     Pavle Obradovic <pobradovic08@gmail.com>
 */

/*
 * Get module's components for a device
 */

use LibreNMS\Util\Number;

$component = new LibreNMS\Component();
$components = $component->getComponents($device['device_id'], ['type' => 'cisco-qfp']);
$components = $components[$device['device_id']];

foreach ($components as $component_id => $tmp_component) {
    $default_graph_array = [
        'from' => \LibreNMS\Config::get('time.day'),
        'to' => \LibreNMS\Config::get('time.now'),
        'id' => $component_id,
        'page' => 'graphs',
    ];

    /*
     * Main container for QFP component
     * Header with system data
     */
    switch ($tmp_component['system_state']) {
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

    switch ($tmp_component['traffic_direction']) {
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

    $text_descr = $tmp_component['name'];
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <div class='pull-left'>
                    <h2 style='margin: 0 5pt 0 0'><i class=\"fa fa-microchip fa-lg icon-theme\" aria-hidden=\"true\"></i></h2>
                </div>
                <h2 class='panel-title'><b>$text_descr</b>
                    <div class='pull-right'>
                        <span class='label {$state_label}'>State: {$tmp_component['system_state']}</span>
                        <span class='label {$direction_label}'>
                            Traffic direction: {$tmp_component['traffic_direction']}
                        </span>
                    </div>
                </h2>
                Last system load at <b>{$tmp_component['system_last_load']}</b>
            </div>";
    echo "<div class='panel-body'>";

    /*
     * QFP Utilization (Load)
     */

    if ($tmp_component['utilization'] < 50) {
        $util_label = 'label-success';
    } elseif ($tmp_component['utilization'] < 75) {
        $util_label = 'label-warning';
    } else {
        $util_label = 'label-danger';
    }

    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_util';
    $text_descr = 'QFP Utilization';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    $text_descr
                    <div class='pull-right'><span class='label {$util_label}'>{$tmp_component['utilization']} %</span></div>
                </h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div>';

    /*
     * Relative QFP utilization to packets processed
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_relativeutil';
    $text_descr = 'QFP Relative utilization per kpps';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div>';

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
                        <span class='label {$packets_label}'>" . Number::formatBi($tmp_component['packets'], 2, 3, 'pps') . '</span>
                    </div>
                </h3>
            </div>';
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div>';

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
                        <span class='label {$throughput_label}'>" . Number::formatBi($tmp_component['throughput'], 2, 3, 'bps') . '</span>
                    </div>
                </h3>
            </div>';
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div>';

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
                        <span class='label {$psize_label}'>" . ceil($tmp_component['average_packet']) . ' Bytes</span>
                    </div>
                </h3>
            </div>';
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div>';

    /*
     * QFP Memory resources
     */
    $mem_prec = $tmp_component['memory_used'] * 100 / $tmp_component['memory_total'];
    if ($mem_prec < 75) {
        $mem_label = 'label-success';
    } elseif ($mem_prec < 90) {
        $mem_label = 'label-warning';
    } else {
        $mem_label = 'label-danger';
    }
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_memory';
    $text_descr = 'QFP Memory';
    $label_text = sprintf('%sB / %sB', Number::formatBi($tmp_component['memory_used'], 2, 3, ''), Number::formatBi($tmp_component['memory_total'], 2, 3, ''));
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    $text_descr
                    <div class='pull-right'><span class='label {$mem_label}'>{$label_text}</span></div>
                </h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div>';
    echo '</div></div>';
}
