<?php

$name = 'suricata';
$unit_text = 'flow ends/s';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 19;

if (isset($vars['sinstance'])) {
    $flow__end__tcp_liberal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_liberal']);
    $flow__end__tcp_state__close_wait_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__close_wait']);
    $flow__end__tcp_state__closed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__closed']);
    $flow__end__tcp_state__closing_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__closing']);
    $flow__end__tcp_state__established_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__established']);
    $flow__end__tcp_state__fin_wait1_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__fin_wait1']);
    $flow__end__tcp_state__fin_wait2_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__fin_wait2']);
    $flow__end__tcp_state__last_ack_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__last_ack']);
    $flow__end__tcp_state__none_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__none']);
    $flow__end__tcp_state__syn_recv_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__syn_recv']);
    $flow__end__tcp_state__syn_sent_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__syn_sent']);
    $flow__end__tcp_state__time_wait_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___flow__end__tcp_state__time_wait']);
} else {
    $flow__end__tcp_liberal_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_liberal']);
    $flow__end__tcp_state__close_wait_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__close_wait']);
    $flow__end__tcp_state__closed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__closed']);
    $flow__end__tcp_state__closing_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__closing']);
    $flow__end__tcp_state__established_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__established']);
    $flow__end__tcp_state__fin_wait1_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__fin_wait1']);
    $flow__end__tcp_state__fin_wait2_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__fin_wait2']);
    $flow__end__tcp_state__last_ack_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__last_ack']);
    $flow__end__tcp_state__none_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__none']);
    $flow__end__tcp_state__syn_recv_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__syn_recv']);
    $flow__end__tcp_state__syn_sent_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__syn_sent']);
    $flow__end__tcp_state__time_wait_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___flow__end__tcp_state__time_wait']);
}

$rrd_list = [];
if (Rrd::checkRrdExists($flow__end__tcp_liberal_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $flow__end__tcp_liberal_rrd_filename,
        'descr' => 'TCP Liberal',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__close_wait_rrd_filename,
        'descr' => 'TCP Close Wait',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__closed_rrd_filename,
        'descr' => 'TCP Closed',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__closing_rrd_filename,
        'descr' => 'TCP Closing',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__established_rrd_filename,
        'descr' => 'TCP Established',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__fin_wait1_rrd_filename,
        'descr' => 'TCP Fin Wait1',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__fin_wait2_rrd_filename,
        'descr' => 'TCP Fin Wait2',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__last_ack_rrd_filename,
        'descr' => 'TCP Last Act',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__none_rrd_filename,
        'descr' => 'TCP None',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__syn_recv_rrd_filename,
        'descr' => 'TCP Syn Recv',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__syn_sent_rrd_filename,
        'descr' => 'TCP Syn Sent',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $flow__end__tcp_state__time_wait_rrd_filename,
        'descr' => 'TCP Time Wait',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
