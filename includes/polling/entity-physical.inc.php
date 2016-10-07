<?php

if ($config['enable_inventory']) {
    // Cisco
    if ($device['os'] == 'ios') {
        include 'includes/polling/entity-physical/ios.inc.php';
    }

    // Cisco CIMC
    if ($device['os'] == 'cimc') {
        include 'includes/polling/entity-physical/cimc.inc.php';
    }

    // Update State
    include 'includes/polling/entity-physical/state.inc.php';
} else {
    echo 'Disabled!';
}//end if
