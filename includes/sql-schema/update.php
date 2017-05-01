<?php

// MYSQL Check - FIXME
// 1 UNKNOWN

/*
 * LibreNMS Network Management and Monitoring System
 * Copyright (C) 2006-2012, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 */

if (!isset($debug)  && php_sapi_name() == 'cli') {
    // Not called from within discovery, let's load up the necessary stuff.
    $init_modules = array();
    require realpath(__DIR__ . '/../..') . '/includes/init.php';

    $options = getopt('d');
    $debug = isset($options['d']);
}

if (db_schema_is_current()) {
    d_echo("DB Schema already up to date.\n");
    return;
}

// Set Database Character set and Collation
dbQuery('ALTER DATABASE ? CHARACTER SET utf8 COLLATE utf8_unicode_ci;', array(array($config['db_name'])));

$db_rev = get_db_schema();
$insert = ($db_rev == 0); // if $db_rev == 0, insert the first update

$updating = 0;
$limit = 150; //magic marker far enough in the future
foreach (get_schema_list() as $file_rev => $file) {
    if ($file_rev > $db_rev) {
        if (isset($_SESSION['stage'])) {
            $limit++;
            if (time()-$_SESSION['last'] > 45) {
                $_SESSION['offset'] = $limit;
                $GLOBALS['refresh'] = '<b>Updating, please wait..</b><sub>'.date('r').'</sub><script>window.location.href = "install.php?offset='.$limit.'";</script>';
                return;
            }
        }

        if (!$updating) {
            echo "-- Updating database schema\n";
        }

        printf('%03d -> %03d ...', $db_rev, $file_rev);

        $err = 0;
        if ($data = file_get_contents($file)) {
            foreach (explode("\n", $data) as $line) {
                if (trim($line)) {
                    d_echo("$line \n");

                    if ($line[0] != '#') {
                        if (!mysqli_query($database_link, $line)) {
                            $err++;
                            d_echo(mysqli_error($database_link) . PHP_EOL);
                        }
                    }
                }
            }

            echo " done ($err errors).\n";
        } else {
            echo " Could not open file! $file\n";
        }//end if

        $updating++;
        $db_rev = $file_rev;
        if ($insert) {
            dbInsert(array('version' => $db_rev), 'dbSchema');
            $insert = false;
        } else {
            dbUpdate(array('version' => $db_rev), 'dbSchema');
        }
    }//end if
}//end foreach

if ($updating) {
    echo "-- Done\n";
    if (isset($_SESSION['stage'])) {
        $_SESSION['build-ok'] = true;
    }
}
