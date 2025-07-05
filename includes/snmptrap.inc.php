<?php

use App\Facades\LibrenmsConfig;

function process_trap($device, $entry)
{
    $oid = trim(strstr($entry[3], ' '));
    $oid = str_replace('::', '', strstr($oid, '::'));

    $file = LibrenmsConfig::get('install_dir') . '/includes/snmptrap/' . $oid . '.inc.php';
    if (is_file($file)) {
        include $file;
    } else {
        echo "unknown trap ($file)";
    }
}
