<?php

$name = 'suricata';
$unit_text = 'GRE pkt/s';
$colours = 'psychedelic';
$descr_len = 25;

if (isset($vars['sinstance'])) {
    $decoder__event__gre__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__pkt_too_small']);
    $decoder__event__gre__version0_flags_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version0_flags']);
    $decoder__event__gre__version0_hdr_too_big_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version0_hdr_too_big']);
    $decoder__event__gre__version0_malformed_sre_hdr_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version0_malformed_sre_hdr']);
    $decoder__event__gre__version0_recur_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version0_recur']);
    $decoder__event__gre__version1_chksum_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version1_chksum']);
    $decoder__event__gre__version1_flags_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version1_flags']);
    $decoder__event__gre__version1_hdr_too_big_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version1_hdr_too_big']);
    $decoder__event__gre__version1_malformed_sre_hdr_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version1_malformed_sre_hdr']);
    $decoder__event__gre__version1_no_key_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version1_no_key']);
    $decoder__event__gre__version1_recur_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version1_recur']);
    $decoder__event__gre__version1_route_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version1_route']);
    $decoder__event__gre__version1_ssr_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version1_ssr']);
    $decoder__event__gre__version1_wrong_protocol_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__version1_wrong_protocol']);
    $decoder__event__gre__wrong_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__gre__wrong_version']);
} else {
    $decoder__event__gre__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__pkt_too_small']);
    $decoder__event__gre__version0_flags_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version0_flags']);
    $decoder__event__gre__version0_hdr_too_big_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version0_hdr_too_big']);
    $decoder__event__gre__version0_malformed_sre_hdr_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version0_malformed_sre_hdr']);
    $decoder__event__gre__version0_recur_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version0_recur']);
    $decoder__event__gre__version1_chksum_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version1_chksum']);
    $decoder__event__gre__version1_flags_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version1_flags']);
    $decoder__event__gre__version1_hdr_too_big_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version1_hdr_too_big']);
    $decoder__event__gre__version1_malformed_sre_hdr_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version1_malformed_sre_hdr']);
    $decoder__event__gre__version1_no_key_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version1_no_key']);
    $decoder__event__gre__version1_recur_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version1_recur']);
    $decoder__event__gre__version1_route_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version1_route']);
    $decoder__event__gre__version1_ssr_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version1_ssr']);
    $decoder__event__gre__version1_wrong_protocol_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__version1_wrong_protocol']);
    $decoder__event__gre__wrong_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__gre__wrong_version']);
}

if (Rrd::checkRrdExists($decoder__event__gre__pkt_too_small_rrd_filename)) {
    $rrd_list[] = [
        'filename' => $decoder__event__gre__pkt_too_small_rrd_filename,
        'descr' => 'Pkt Too Small',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version0_flags_rrd_filename,
        'descr' => 'Version0 Flags',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version0_hdr_too_big_rrd_filename,
        'descr' => 'Version0 Hdr Too Big',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version0_malformed_sre_hdr_rrd_filename,
        'descr' => 'Version0 Malfrm Sre Hdr',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version0_recur_rrd_filename,
        'descr' => 'Version0 Recur',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version1_chksum_rrd_filename,
        'descr' => 'Version1 Chksum',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version1_flags_rrd_filename,
        'descr' => 'Version1 Flags',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version1_malformed_sre_hdr_rrd_filename,
        'descr' => 'Version1 Malfrm Sre Hdr',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version1_no_key_rrd_filename,
        'descr' => 'Version1 No Key',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version1_recur_rrd_filename,
        'descr' => 'Version1 Recur',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version1_route_rrd_filename,
        'descr' => 'Version1 Route',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version1_ssr_rrd_filename,
        'descr' => 'Version1 SSR',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__version1_wrong_protocol_rrd_filename,
        'descr' => 'Version1 Wrong Proto',
        'ds' => 'data',
    ];
    $rrd_list[] = [
        'filename' => $decoder__event__gre__wrong_version_rrd_filename,
        'descr' => 'Version1 Wrong Ver',
        'ds' => 'data',
    ];
} else {
    d_echo('RRD "' . $decoder__event__gre__pkt_too_small_rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
