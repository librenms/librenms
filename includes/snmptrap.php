<?php

function snmptrap($entry) {
    global $config;
    // Formatting array
    $hostname = trim($entry[0]);
    $ip = str_replace(array("UDP:","[","]"), "", $entry[1]);
    $ip = trim(strstr($ip, ":", true));
    $oid = trim(strstr($entry[3], " "));
    $oid = str_replace("::", "", strstr($oid, "::"));
    $who = trim(strstr($entry[4], " "));

    $device = @dbFetchRow('SELECT * FROM devices WHERE `hostname` = ?', [$hostname]);

    if (!$device['device_id']) {
        $device = @dbFetchRow('SELECT * FROM ipv4_addresses AS A, ports AS I WHERE A.ipv4_address = ? AND I.port_id = A.port_id', [$ip]);
    }

    if (!$device['device_id']) {
        echo "unknown device\n";
        exit;
    }

    $file = $config['install_dir'].'/includes/snmptrap/'.$oid.'.inc.php';
    if (is_file($file)) {
        include "$file";
    } else {
        echo "unknown trap ($file)";
    }
}
