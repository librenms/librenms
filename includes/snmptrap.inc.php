<?php

function process_trap($device, $entry) {
    global $config;

    $oid = trim(strstr($entry[3], " "));
    $oid = str_replace("::", "", strstr($oid, "::"));

    $file = $config['install_dir'].'/includes/snmptrap/'.$oid.'.inc.php';

    if (is_file($file)) {
        include "$file";
    } else {
        echo "unknown trap ($file)";
    }    
}
