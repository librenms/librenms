<?php

require_once 'includes/ss-shared.inc.php';

$allowed_sockets = $app->data['allowed_sockets'] ?? [];
$allowed_afs = $app->data['allowed_afs'] ?? [];

$rrdArray = [];

foreach ($ss_section_list as $gen_type) {
    // Don't display data for filtered sockets and address families.
    if (! in_array($gen_type, $allowed_sockets) && ! in_array($gen_type, $allowed_afs)) {
        continue;
    }

    // Display graphs for address families with netids.
    if (array_key_exists($gen_type, $ss_netid_mapper)) {
        foreach ($ss_netid_mapper[$gen_type] as $netid) {
            // Don't display data for filtered sockets.
            if (! in_array($netid, $allowed_sockets)) {
                continue;
            }
            $socket_type = $gen_type . '_' . $netid;

            // Map the socket type to its available statues.
            if (array_key_exists($gen_type, $ss_socket_states_mapper)) {
                $local_ss_socket_states_mapper = $ss_socket_states_mapper[$gen_type];
            } else {
                $local_ss_socket_states_mapper = $ss_socket_states_mapper['default'];
            }

            foreach ($local_ss_socket_states_mapper as $socket_state => $socket_state_clean_name) {
                $rrdArray[$socket_type][$socket_state_clean_name] = [
                    'descr' => $socket_type . '_' . $socket_state_clean_name,
                ];
            }
        }
    } else {
        // Map the socket type to its available statues.
        if (array_key_exists($gen_type, $ss_socket_states_mapper)) {
            $local_ss_socket_states_mapper = $ss_socket_states_mapper[$gen_type];
        } else {
            $local_ss_socket_states_mapper = $ss_socket_states_mapper['default'];
        }

        foreach ($local_ss_socket_states_mapper as $socket_state => $socket_state_clean_name) {
            $rrdArray[$gen_type][$socket_state_clean_name] = ['descr' => $gen_type . '_' . $socket_state_clean_name];
        }
    }
}

require 'ss-common.inc.php';
