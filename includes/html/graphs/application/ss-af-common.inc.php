<?php

require 'includes/ss-shared.inc.php';

$allowed_afs = $app->data['allowed_afs'] ?? [];
$rrdArray = [];

// This section draws the individual graphs in the device application page
// displaying the SPECIFIED address-family's socket type's states.
if (isset($vars['netid'])) {
    $netid = $vars['netid'];
    $af_netid = $addr_family . '_' . $netid;

    // Map the socket type to its available statuses.
    if (array_key_exists($netid, $ss_socket_states_mapper)) {
        $local_ss_socket_states_mapper = $ss_socket_states_mapper[$netid];
    } else {
        $local_ss_socket_states_mapper = $ss_socket_states_mapper['default'];
    }

    // Create the rrdArray and map the address family's socket type
    // to its "clean" socket state name and description.
    foreach ($local_ss_socket_states_mapper as $socket_state => $socket_state_clean_name) {
        $rrdArray[$af_netid][$socket_state_clean_name] = [
            'descr' => $socket_state_clean_name,
        ];
    }
}

// This section draws the graph for the application-specific pages
// displaying ALL of the address-family's sockets types' states.
if (! isset($vars['netid']) && in_array($addr_family, $allowed_afs)) {
    $allowed_sockets = $app->data['allowed_sockets'] ?? [];

    foreach ($ss_netid_mapper[$addr_family] as $netid) {
        // Don't display data for filtered sockets.
        if (! in_array($netid, $allowed_sockets)) {
            continue;
        }
        $af_netid = $addr_family . '_' . $netid;

        // Map the socket type to its available statuses.
        if (array_key_exists($netid, $ss_socket_states_mapper)) {
            $local_ss_socket_states_mapper = $ss_socket_states_mapper[$netid];
        } else {
            $local_ss_socket_states_mapper = $ss_socket_states_mapper['default'];
        }

        // Create the rrdArray and map the address family's socket type
        // to its "clean" socket state name and description.
        foreach ($local_ss_socket_states_mapper as $socket_state => $socket_state_clean_name) {
            $rrdArray[$af_netid][$socket_state_clean_name] = [
                'descr' => $af_netid . '_' . $socket_state_clean_name,
            ];
        }
    }
}

require 'ss-common.inc.php';
