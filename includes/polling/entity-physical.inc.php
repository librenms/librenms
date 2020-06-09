<?php

use LibreNMS\Config;

if (Config::get('enable_inventory')) {
    if (file_exists(Config::get('install_dir') . "/includes/polling/entity-physical/{$device['os']}.inc.php")) {
        include Config::get('install_dir') . "/includes/polling/entity-physical/{$device['os']}.inc.php";
    }

    // Update State
    include 'includes/polling/entity-physical/state.inc.php';
} else {
    echo 'Disabled!';
}//end if

unset(
    $mod_stats,
    $chan_stats
);
