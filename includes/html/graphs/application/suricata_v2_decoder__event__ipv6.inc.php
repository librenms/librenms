<?php

$name = 'suricata';
$unit_text = 'IPv6 pkt/s';
$colours = 'psychedelic';
$descr_len = 22;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__data_after_none_header']);
    $decoder__event__ipv6__dstopts_only_padding_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__dstopts_only_padding']);
    $decoder__event__ipv6__dstopts_unknown_opt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__dstopts_unknown_opt']);
    $decoder__event__ipv6__exthdr_ah_res_not_null_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__exthdr_ah_res_not_null']);
    $decoder__event__ipv6__exthdr_dupl_ah_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__exthdr_dupl_ah']);
    $decoder__event__ipv6__exthdr_dupl_dh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__exthdr_dupl_dh']);
    $decoder__event__ipv6__exthdr_dupl_eh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__exthdr_dupl_eh']);
    $decoder__event__ipv6__exthdr_dupl_fh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__exthdr_dupl_fh']);
    $decoder__event__ipv6__exthdr_dupl_hh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__exthdr_dupl_hh']);
    $decoder__event__ipv6__exthdr_dupl_rh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__exthdr_dupl_rh']);
    $decoder__event__ipv6__exthdr_invalid_optlen_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__exthdr_invalid_optlen']);
    $decoder__event__ipv6__exthdr_useless_fh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__exthdr_useless_fh']);
    $decoder__event__ipv6__fh_non_zero_reserved_field_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__fh_non_zero_reserved_field']);
    $decoder__event__ipv6__frag_ignored_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__frag_ignored']);
    $decoder__event__ipv6__frag_invalid_length_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__frag_invalid_length']);
    $decoder__event__ipv6__frag_overlap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__frag_overlap']);
    $decoder__event__ipv6__frag_pkt_too_large_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__frag_pkt_too_large']);
    $decoder__event__ipv6__hopopts_only_padding_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__hopopts_only_padding']);
    $decoder__event__ipv6__hopopts_unknown_opt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__hopopts_unknown_opt']);
    $decoder__event__ipv6__icmpv4_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__icmpv4']);
    $decoder__event__ipv6__ipv4_in_ipv6_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__ipv4_in_ipv6_too_small']);
    $decoder__event__ipv6__ipv4_in_ipv6_wrong_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__ipv4_in_ipv6_wrong_version']);
    $decoder__event__ipv6__ipv6_in_ipv6_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__ipv6_in_ipv6_too_small']);
    $decoder__event__ipv6__ipv6_in_ipv6_wrong_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__ipv6_in_ipv6_wrong_version']);
    $decoder__event__ipv6__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__pkt_too_small']);
    $decoder__event__ipv6__rh_type_0_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__rh_type_0']);
    $decoder__event__ipv6__trunc_exthdr_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__trunc_exthdr']);
    $decoder__event__ipv6__trunc_pkt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__trunc_pkt']);
    $decoder__event__ipv6__unknown_next_header_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__unknown_next_header']);
    $decoder__event__ipv6__wrong_ip_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__wrong_ip_version']);
    $decoder__event__ipv6__zero_len_padn_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv6__zero_len_padn']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__data_after_none_header']);
    $decoder__event__ipv6__dstopts_only_padding_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__dstopts_only_padding']);
    $decoder__event__ipv6__dstopts_unknown_opt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__dstopts_unknown_opt']);
    $decoder__event__ipv6__exthdr_ah_res_not_null_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__exthdr_ah_res_not_null']);
    $decoder__event__ipv6__exthdr_dupl_ah_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__exthdr_dupl_ah']);
    $decoder__event__ipv6__exthdr_dupl_dh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__exthdr_dupl_dh']);
    $decoder__event__ipv6__exthdr_dupl_eh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__exthdr_dupl_eh']);
    $decoder__event__ipv6__exthdr_dupl_fh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__exthdr_dupl_fh']);
    $decoder__event__ipv6__exthdr_dupl_hh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__exthdr_dupl_hh']);
    $decoder__event__ipv6__exthdr_dupl_rh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__exthdr_dupl_rh']);
    $decoder__event__ipv6__exthdr_invalid_optlen_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__exthdr_invalid_optlen']);
    $decoder__event__ipv6__exthdr_useless_fh_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__exthdr_useless_fh']);
    $decoder__event__ipv6__fh_non_zero_reserved_field_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__fh_non_zero_reserved_field']);
    $decoder__event__ipv6__frag_ignored_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__frag_ignored']);
    $decoder__event__ipv6__frag_invalid_length_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__frag_invalid_length']);
    $decoder__event__ipv6__frag_overlap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__frag_overlap']);
    $decoder__event__ipv6__frag_pkt_too_large_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__frag_pkt_too_large']);
    $decoder__event__ipv6__hopopts_only_padding_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__hopopts_only_padding']);
    $decoder__event__ipv6__hopopts_unknown_opt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__hopopts_unknown_opt']);
    $decoder__event__ipv6__icmpv4_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__icmpv4']);
    $decoder__event__ipv6__ipv4_in_ipv6_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__ipv4_in_ipv6_too_small']);
    $decoder__event__ipv6__ipv4_in_ipv6_wrong_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__ipv4_in_ipv6_wrong_version']);
    $decoder__event__ipv6__ipv6_in_ipv6_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__ipv6_in_ipv6_too_small']);
    $decoder__event__ipv6__ipv6_in_ipv6_wrong_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__ipv6_in_ipv6_wrong_version']);
    $decoder__event__ipv6__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__pkt_too_small']);
    $decoder__event__ipv6__rh_type_0_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__rh_type_0']);
    $decoder__event__ipv6__trunc_exthdr_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__trunc_exthdr']);
    $decoder__event__ipv6__trunc_pkt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__trunc_pkt']);
    $decoder__event__ipv6__unknown_next_header_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__unknown_next_header']);
    $decoder__event__ipv6__wrong_ip_version_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__wrong_ip_version']);
    $decoder__event__ipv6__zero_len_padn_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv6__zero_len_padn']);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Data After None Hdr',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__dstopts_only_padding_rrd_filename,
        'descr' => 'DSTopts Only Padding',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__dstopts_unknown_opt_rrd_filename,
        'descr' => 'DSTopts Unknown Opt',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__exthdr_ah_res_not_null_rrd_filename,
        'descr' => 'Exthdr AH Res Not Null',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__exthdr_dupl_ah_rrd_filename,
        'descr' => 'Exthdr Dupl AH',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__exthdr_dupl_dh_rrd_filename,
        'descr' => 'Exthdr Dupl DH',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__exthdr_dupl_eh_rrd_filename,
        'descr' => 'Exthdr Dupl EH',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__exthdr_dupl_fh_rrd_filename,
        'descr' => 'Exthdr Dupl FH',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__exthdr_dupl_hh_rrd_filename,
        'descr' => 'Exthdr Dupl HH',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__exthdr_dupl_rh_rrd_filename,
        'descr' => 'Exthdr Dupl RH',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__exthdr_invalid_optlen_rrd_filename,
        'descr' => 'Exthdr Invalid Optlen',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__exthdr_useless_fh_rrd_filename,
        'descr' => 'Exthdr Useless FH',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__fh_non_zero_reserved_field_rrd_filename,
        'descr' => 'FH Non-zero Resv Fld',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__frag_ignored_rrd_filename,
        'descr' => 'Frag Ignored',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__frag_invalid_length_rrd_filename,
        'descr' => 'Frag Invalid Length',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__frag_overlap_rrd_filename,
        'descr' => 'Frag Overlay',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__frag_pkt_too_large_rrd_filename,
        'descr' => 'Frag Pkt To Large',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__hopopts_only_padding_rrd_filename,
        'descr' => 'Hopopts Only Padding',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__hopopts_unknown_opt_rrd_filename,
        'descr' => 'Hopopts Unknown Opt',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__icmpv4_rrd_filename,
        'descr' => 'ICMPv4',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__ipv4_in_ipv6_too_small_rrd_filename,
        'descr' => 'IPv6 in IPv4 Too Smll',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__ipv4_in_ipv6_wrong_version_rrd_filename,
        'descr' => 'IPv4 in IPv6 Wrong Ver',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__ipv6_in_ipv6_too_small_rrd_filename,
        'descr' => 'IPv6 in IPv6 Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__ipv6_in_ipv6_wrong_version_rrd_filename,
        'descr' => 'IPv6 In IPv6 Wrong Ver',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__pkt_too_small_rrd_filename,
        'descr' => 'Pkt Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__rh_type_0_rrd_filename,
        'descr' => 'RH Type 0',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__trunc_exthdr_rrd_filename,
        'descr' => 'Trunc Exthdr',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__trunc_pkt_rrd_filename,
        'descr' => 'Trunc Pkt',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__unknown_next_header_rrd_filename,
        'descr' => 'Unknown Next Hdr',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__wrong_ip_version_rrd_filename,
        'descr' => 'Wrong IP Ver',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv6__zero_len_padn_rrd_filename,
        'descr' => 'Zero Len Padn',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
