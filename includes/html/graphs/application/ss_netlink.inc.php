<?php

require_once 'includes/ss-shared.inc.php';

$addr_family = 'netlink';
$rrdArray = [];

if (array_key_exists($addr_family, $ss_socket_states_mapper)) {
    $local_ss_socket_states_mapper = $ss_socket_states_mapper[$addr_family];
} else {
    $local_ss_socket_states_mapper = $ss_socket_states_mapper['default'];
}

foreach ($local_ss_socket_states_mapper as $socket_state => $socket_state_clean_name) {
    $rrdArray[$addr_family][$socket_state_clean_name] = ['descr' => $socket_state_clean_name];
}

require 'ss-common.inc.php';
