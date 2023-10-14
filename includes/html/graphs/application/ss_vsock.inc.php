<?php

require_once 'includes/ss-shared.inc.php';

$addr_family = 'vsock';
$rrdArray = [];

$netid = $vars['netid'];

$socket_type = $addr_family . '_' . $netid;

if (array_key_exists($netid, $ss_socket_states_mapper)) {
    $local_ss_socket_states_mapper = $ss_socket_states_mapper[$netid];
} else {
    $local_ss_socket_states_mapper = $ss_socket_states_mapper['default'];
}

foreach ($local_ss_socket_states_mapper as $socket_state => $socket_state_clean_name) {
    $rrdArray[$socket_type][$socket_state_clean_name] = [
        'descr' => $socket_state_clean_name,
    ];
}

require 'ss-common.inc.php';
