<?php

use LibreNMS\Config;

function process_trap($device, $entry)
{
    $oid = trim(strstr($entry[3], ' '));
    $oid = str_replace('::', '', strstr($oid, '::'));

    $file = Config::get('install_dir') . '/includes/snmptrap/' . $oid . '.inc.php';
    if (is_file($file)) {
        include $file;
    } else {
        echo "unknown trap ($file)";
    }
}
