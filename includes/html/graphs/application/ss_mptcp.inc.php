<?php

require_once 'includes/ss-shared.inc.php';

$socket_type = 'mptcp';
$rrdArray = [];

if (array_key_exists($socket_type, $ss_socket_states_mapper)) {
    $local_ss_socket_states_mapper = $ss_socket_states_mapper[$socket_type];
} else {
    $local_ss_socket_states_mapper = $ss_socket_states_mapper['default'];
}

foreach ($local_ss_socket_states_mapper as $socket_state => $socket_state_clean_name) {
    $rrdArray[$socket_type][$socket_state_clean_name] = ['descr' => $socket_state_clean_name];
}

require 'ss-common.inc.php';
