<?php

/*
 * RRD groups shared between the HAProxy poller and graphs.
 *
 * Each item, frontend/backend/server/listener, gets one RRD per group
 * that applies to it, named like '<type>_-_<item>_-_<group>'. The totals
 * use the same groups, named like 'totals_-_<group>'.
 *
 * group => [
 *     'types' => types it is valid for... fe, be, srv, li
 *     'http' => if it is only for http mode,
 *     'totals' => if it is summed for the totals,
 *     'gated' => if only created once non-zero values have been seen for an item,
 *     'fields' => [CSV column => [DS name, DS type]],
 * ]
 *
 * DS names are limited to 19 chars, so the longer ones are shortened.
 * DS names must be unique across all groups, as they are also used for
 * the metric names.
 */

return [
    'groups' => [
        'core' => [
            'types' => ['fe', 'be', 'srv', 'li'],
            'http' => false,
            'totals' => true,
            'gated' => false,
            'fields' => [
                'status' => ['status', 'GAUGE'],
                // counters
                'bin' => ['bin', 'DERIVE'],
                'bout' => ['bout', 'DERIVE'],
                'stot' => ['stot', 'DERIVE'],
                'req_tot' => ['req_tot', 'DERIVE'],
                'conn_tot' => ['conn_tot', 'DERIVE'],
                'lbtot' => ['lbtot', 'DERIVE'],
                'connect' => ['connect', 'DERIVE'],
                'reuse' => ['reuse', 'DERIVE'],
                'ereq' => ['ereq', 'DERIVE'],
                'econ' => ['econ', 'DERIVE'],
                'eresp' => ['eresp', 'DERIVE'],
                'eint' => ['eint', 'DERIVE'],
                'wrew' => ['wrew', 'DERIVE'],
                'dreq' => ['dreq', 'DERIVE'],
                'dresp' => ['dresp', 'DERIVE'],
                'dcon' => ['dcon', 'DERIVE'],
                'dses' => ['dses', 'DERIVE'],
                'intercepted' => ['intercepted', 'DERIVE'],
                'ssl_sess' => ['ssl_sess', 'DERIVE'],
                'ssl_reused_sess' => ['ssl_reused_sess', 'DERIVE'],
                'ssl_failed_handshake' => ['ssl_failed_hs', 'DERIVE'],
                'ssl_ocsp_staple' => ['ssl_ocsp_staple', 'DERIVE'],
                'ssl_failed_ocsp_staple' => ['ssl_failed_ocsp', 'DERIVE'],
                // gauges
                'scur' => ['scur', 'GAUGE'],
                'smax' => ['smax', 'GAUGE'],
                'slim' => ['slim', 'GAUGE'],
                'rate' => ['rate', 'GAUGE'],
                'rate_max' => ['rate_max', 'GAUGE'],
                'rate_lim' => ['rate_lim', 'GAUGE'],
                'conn_rate' => ['conn_rate', 'GAUGE'],
                'conn_rate_max' => ['conn_rate_max', 'GAUGE'],
                'req_rate' => ['req_rate', 'GAUGE'],
                'req_rate_max' => ['req_rate_max', 'GAUGE'],
            ],
        ],
        'be' => [
            'types' => ['be', 'srv'],
            'http' => false,
            'totals' => true,
            'gated' => false,
            'fields' => [
                // counters
                'wretr' => ['wretr', 'DERIVE'],
                'wredis' => ['wredis', 'DERIVE'],
                'cli_abrt' => ['cli_abrt', 'DERIVE'],
                'srv_abrt' => ['srv_abrt', 'DERIVE'],
                'chkfail' => ['chkfail', 'DERIVE'],
                'chkdown' => ['chkdown', 'DERIVE'],
                'downtime' => ['downtime', 'DERIVE'],
                // gauges
                'qcur' => ['qcur', 'GAUGE'],
                'qmax' => ['qmax', 'GAUGE'],
                'qtime' => ['qtime', 'GAUGE'],
                'ctime' => ['ctime', 'GAUGE'],
                'rtime' => ['rtime', 'GAUGE'],
                'ttime' => ['ttime', 'GAUGE'],
                'qtime_max' => ['qtime_max', 'GAUGE'],
                'ctime_max' => ['ctime_max', 'GAUGE'],
                'rtime_max' => ['rtime_max', 'GAUGE'],
                'ttime_max' => ['ttime_max', 'GAUGE'],
                'weight' => ['weight', 'GAUGE'],
                'uweight' => ['uweight', 'GAUGE'],
                'act' => ['act', 'GAUGE'],
                'bck' => ['bck', 'GAUGE'],
            ],
        ],
        'health' => [
            'types' => ['srv'],
            'http' => false,
            'totals' => false,
            'gated' => false,
            'fields' => [
                'check_duration' => ['check_duration', 'GAUGE'],
                'check_health' => ['check_health', 'GAUGE'],
                'throttle' => ['throttle', 'GAUGE'],
                'idle_conn_cur' => ['idle_conn_cur', 'GAUGE'],
                'used_conn_cur' => ['used_conn_cur', 'GAUGE'],
                'safe_conn_cur' => ['safe_conn_cur', 'GAUGE'],
                'need_conn_est' => ['need_conn_est', 'GAUGE'],
                'srv_icur' => ['srv_icur', 'GAUGE'],
            ],
        ],
        'http' => [
            'types' => ['fe', 'be', 'srv'],
            'http' => true,
            'totals' => true,
            'gated' => false,
            'fields' => [
                'hrsp_1xx' => ['hrsp_1xx', 'DERIVE'],
                'hrsp_2xx' => ['hrsp_2xx', 'DERIVE'],
                'hrsp_3xx' => ['hrsp_3xx', 'DERIVE'],
                'hrsp_4xx' => ['hrsp_4xx', 'DERIVE'],
                'hrsp_5xx' => ['hrsp_5xx', 'DERIVE'],
                'hrsp_other' => ['hrsp_other', 'DERIVE'],
                'comp_in' => ['comp_in', 'DERIVE'],
                'comp_out' => ['comp_out', 'DERIVE'],
                'comp_byp' => ['comp_byp', 'DERIVE'],
                'comp_rsp' => ['comp_rsp', 'DERIVE'],
                'cache_lookups' => ['cache_lookups', 'DERIVE'],
                'cache_hits' => ['cache_hits', 'DERIVE'],
                'h1req' => ['h1req', 'DERIVE'],
                'h2req' => ['h2req', 'DERIVE'],
                'h3req' => ['h3req', 'DERIVE'],
                'req_other' => ['req_other', 'DERIVE'],
                'h1sess' => ['h1sess', 'DERIVE'],
                'h2sess' => ['h2sess', 'DERIVE'],
                'h3sess' => ['h3sess', 'DERIVE'],
                'sess_other' => ['sess_other', 'DERIVE'],
            ],
        ],
        'h1' => [
            'types' => ['fe', 'be'],
            'http' => true,
            'totals' => true,
            'gated' => false,
            'fields' => [
                // counters
                'h1_bytes_in' => ['h1_bytes_in', 'DERIVE'],
                'h1_bytes_out' => ['h1_bytes_out', 'DERIVE'],
                'h1_spliced_bytes_in' => ['h1_splice_bytes_in', 'DERIVE'],
                'h1_spliced_bytes_out' => ['h1_splice_bytes_out', 'DERIVE'],
                'h1_total_connections' => ['h1_tot_conn', 'DERIVE'],
                'h1_total_streams' => ['h1_tot_streams', 'DERIVE'],
                // gauges
                'h1_open_connections' => ['h1_open_conn', 'GAUGE'],
                'h1_open_streams' => ['h1_open_streams', 'GAUGE'],
            ],
        ],
        // HTTP/2... these are zero when HTTP/2 is not in use, so they are only
        // created once an item has non-zero values
        'h2' => [
            'types' => ['fe', 'be'],
            'http' => true,
            'totals' => true,
            'gated' => true,
            'fields' => [
                // counters
                'h2_total_connections' => ['h2_tot_conn', 'DERIVE'],
                'h2_backend_total_streams' => ['h2_be_tot_streams', 'DERIVE'],
                'h2_headers_rcvd' => ['h2_headers_rcvd', 'DERIVE'],
                'h2_data_rcvd' => ['h2_data_rcvd', 'DERIVE'],
                'h2_settings_rcvd' => ['h2_settings_rcvd', 'DERIVE'],
                'h2_rst_stream_rcvd' => ['h2_rst_stream_rcvd', 'DERIVE'],
                'h2_goaway_rcvd' => ['h2_goaway_rcvd', 'DERIVE'],
                'h2_rst_stream_resp' => ['h2_rst_stream_resp', 'DERIVE'],
                'h2_goaway_resp' => ['h2_goaway_resp', 'DERIVE'],
                'h2_detected_conn_protocol_errors' => ['h2_conn_proto_err', 'DERIVE'],
                'h2_detected_strm_protocol_errors' => ['h2_strm_proto_err', 'DERIVE'],
                // gauges
                'h2_open_connections' => ['h2_open_conn', 'GAUGE'],
                'h2_backend_open_streams' => ['h2_be_open_streams', 'GAUGE'],
            ],
        ],
        // HTTP/3 and QUIC... these are always zero for frontends without a QUIC
        // bind, so they are only created once a frontend has non-zero values
        'quic' => [
            'types' => ['fe'],
            'http' => true,
            'totals' => true,
            'gated' => true,
            'fields' => [
                // HTTP/3 frames
                'h3_data' => ['h3_data', 'DERIVE'],
                'h3_headers' => ['h3_headers', 'DERIVE'],
                'h3_cancel_push' => ['h3_cancel_push', 'DERIVE'],
                'h3_push_promise' => ['h3_push_promise', 'DERIVE'],
                'h3_max_push_id' => ['h3_max_push_id', 'DERIVE'],
                'h3_goaway' => ['h3_goaway', 'DERIVE'],
                'h3_settings' => ['h3_settings', 'DERIVE'],
                // HTTP/3 errors
                'h3_no_error' => ['h3_no_error', 'DERIVE'],
                'h3_general_protocol_error' => ['h3_gen_proto_err', 'DERIVE'],
                'h3_internal_error' => ['h3_internal_error', 'DERIVE'],
                'h3_stream_creation_error' => ['h3_strm_create_err', 'DERIVE'],
                'h3_closed_critical_stream' => ['h3_closed_crit_strm', 'DERIVE'],
                'h3_frame_unexpected' => ['h3_frame_unexpected', 'DERIVE'],
                'h3_frame_error' => ['h3_frame_error', 'DERIVE'],
                'h3_excessive_load' => ['h3_excessive_load', 'DERIVE'],
                'h3_id_error' => ['h3_id_error', 'DERIVE'],
                'h3_settings_error' => ['h3_settings_error', 'DERIVE'],
                'h3_missing_settings' => ['h3_missing_settings', 'DERIVE'],
                'h3_request_rejected' => ['h3_req_rejected', 'DERIVE'],
                'h3_request_cancelled' => ['h3_req_cancelled', 'DERIVE'],
                'h3_request_incomplete' => ['h3_req_incomplete', 'DERIVE'],
                'h3_message_error' => ['h3_message_error', 'DERIVE'],
                'h3_connect_error' => ['h3_connect_error', 'DERIVE'],
                'h3_version_fallback' => ['h3_version_fallback', 'DERIVE'],
                // QPACK errors, the missing q is how HAProxy names it
                'pack_decompression_failed' => ['qpack_decomp_failed', 'DERIVE'],
                'qpack_encoder_stream_error' => ['qpack_enc_strm_err', 'DERIVE'],
                'qpack_decoder_stream_error' => ['qpack_dec_strm_err', 'DERIVE'],
                // QUIC packets
                'quic_sent_pkt' => ['quic_sent_pkt', 'DERIVE'],
                'quic_lost_pkt' => ['quic_lost_pkt', 'DERIVE'],
                'quic_dropped_pkt' => ['quic_dropped_pkt', 'DERIVE'],
                'quic_dropped_pkt_bufoverrun' => ['quic_drop_bufovrrun', 'DERIVE'],
                'quic_dropped_parsing_pkt' => ['quic_drop_parse_pkt', 'DERIVE'],
                'quic_too_short_dgram' => ['quic_too_short_dgrm', 'DERIVE'],
                'quic_socket_full' => ['quic_socket_full', 'DERIVE'],
                'quic_sendto_err' => ['quic_sendto_err', 'DERIVE'],
                'quic_sendto_err_unknwn' => ['quic_sendto_unknwn', 'DERIVE'],
                'quic_rxbuf_full' => ['quic_rxbuf_full', 'DERIVE'],
                // QUIC connections
                'quic_hdshk_fail' => ['quic_hdshk_fail', 'DERIVE'],
                'quic_retry_sent' => ['quic_retry_sent', 'DERIVE'],
                'quic_retry_validated' => ['quic_retry_valid', 'DERIVE'],
                'quic_retry_error' => ['quic_retry_error', 'DERIVE'],
                'quic_stless_rst_sent' => ['quic_stless_rst', 'DERIVE'],
                'quic_conn_migration_done' => ['quic_conn_migration', 'DERIVE'],
                'quic_half_open_conn' => ['quic_half_open_conn', 'GAUGE'],
                // QUIC flow control
                'quic_data_blocked' => ['quic_data_blocked', 'DERIVE'],
                'quic_stream_data_blocked' => ['quic_strm_data_blkd', 'DERIVE'],
                'quic_streams_blocked_bidi' => ['quic_strm_blkd_bidi', 'DERIVE'],
                'quic_streams_blocked_uni' => ['quic_strm_blkd_uni', 'DERIVE'],
                // QUIC transport errors
                'quic_transp_err_no_error' => ['qte_no_error', 'DERIVE'],
                'quic_transp_err_internal_error' => ['qte_internal', 'DERIVE'],
                'quic_transp_err_connection_refused' => ['qte_conn_refused', 'DERIVE'],
                'quic_transp_err_flow_control_error' => ['qte_flow_control', 'DERIVE'],
                'quic_transp_err_stream_limit_error' => ['qte_stream_limit', 'DERIVE'],
                'quic_transp_err_stream_state_error' => ['qte_stream_state', 'DERIVE'],
                'quic_transp_err_final_size_error' => ['qte_final_size', 'DERIVE'],
                'quic_transp_err_frame_encoding_error' => ['qte_frame_encoding', 'DERIVE'],
                'quic_transp_err_transport_parameter_error' => ['qte_transport_param', 'DERIVE'],
                'quic_transp_err_connection_id_limit' => ['qte_conn_id_limit', 'DERIVE'],
                'quic_transp_err_protocol_violation_error' => ['qte_proto_violation', 'DERIVE'],
                'quic_transp_err_invalid_token' => ['qte_invalid_token', 'DERIVE'],
                'quic_transp_err_application_error' => ['qte_app_error', 'DERIVE'],
                'quic_transp_err_crypto_buffer_exceeded' => ['qte_crypto_buf_exc', 'DERIVE'],
                'quic_transp_err_key_update_error' => ['qte_key_update', 'DERIVE'],
                'quic_transp_err_aead_limit_reached' => ['qte_aead_limit', 'DERIVE'],
                'quic_transp_err_no_viable_path' => ['qte_no_viable_path', 'DERIVE'],
                'quic_transp_err_crypto_error' => ['qte_crypto_error', 'DERIVE'],
                'quic_transp_err_unknown_error' => ['qte_unknown', 'DERIVE'],
            ],
        ],
    ],
    // DS that do not make sense to sum for the totals
    'no_total' => [
        'status', 'smax', 'slim', 'rate_max', 'rate_lim', 'conn_rate_max', 'req_rate_max',
        'qmax', 'qtime', 'ctime', 'rtime', 'ttime', 'qtime_max', 'ctime_max', 'rtime_max', 'ttime_max',
        'weight', 'uweight',
    ],
];
