<?php

require 'includes/ss-shared.inc.php';

$rrdArray = [];

// Map the socket type to its available statuses.
if (array_key_exists($netid, $ss_socket_states_mapper)) {
    $local_ss_socket_states_mapper = $ss_socket_states_mapper[$netid];
} else {
    $local_ss_socket_states_mapper = $ss_socket_states_mapper['default'];
}

// Create the rrdArray and map the address family's socket type
// to its "clean" socket state name and description.
foreach ($local_ss_socket_states_mapper as $socket_state => $socket_state_clean_name) {
    $rrdArray[$netid][$socket_state_clean_name] = ['descr' => $socket_state_clean_name];
}

require 'ss-common.inc.php';
