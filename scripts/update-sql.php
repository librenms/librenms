#!/usr/bin/env php
<?php

chdir(realpath(__DIR__ . '/..')); // cwd to the parent directory of this script

// MYSQL Check - FIXME
// 1 UPDATE
require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/functions.php';

if ($fd = @fopen($argv[1], 'r')) {
    $data = fread($fd, 4096);
    while (!feof($fd)) {
        $data .= fread($fd, 4096);
    }

    foreach (explode("\n", $data) as $line) {
        $update = dbQuery($line);
//            mysqli_query($line);
        // FIXME check query success?
        echo "$line \n";
        var_dump($update);
    }
} else {
    echo "ERROR: Could not open file \"$argv[1]\".\n";
}
