<?php

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

if (!isset($init_modules)  && php_sapi_name() == 'cli') {
    // Not called from within discovery, let's load up the necessary stuff.
    $init_modules = array();
    require realpath(__DIR__ . '/../..') . '/includes/init.php';
}

if (isset($skip_schema_lock) && $skip_schema_lock) {
    $schemaLock = true;
} else {
    $schemaLock = \LibreNMS\FileLock::lock('schema', 30);
}

if ($schemaLock === false) {
    echo "Failed to acquire lock, skipping schema update\n";
    $return = 1;
} else {
    $return = 0;

    // only import build.sql to an empty database
    $tables = dbFetchRows("SHOW TABLES FROM {$config['db_name']}");
    if (empty($tables)) {
        echo "-- Creating base database structure\n";
        $step = 0;
        $sql_fh = fopen('build.sql', 'r');
        if ($sql_fh === false) {
            echo 'ERROR: Cannot open SQL build script ' . $sql_file . PHP_EOL;
            $return = 1;
        }

        while (!feof($sql_fh)) {
            $line = fgetss($sql_fh);
            echo 'Step #' . $step++ . ' ...' . PHP_EOL;

            if (!empty($line)) {
                $creation = dbQuery($line);
                if (!$creation) {
                    echo 'WARNING: Cannot execute query (' . $line . '): ' . mysqli_error($database_link) . "\n";
                    $return = 1;
                }
            }
        }

        fclose($sql_fh);
    }


    d_echo("DB Schema update started....\n");

    if (db_schema_is_current()) {
        d_echo("DB Schema already up to date.\n");
    } else {
        // Set Database Character set and Collation
        dbQuery('ALTER DATABASE ? CHARACTER SET utf8 COLLATE utf8_unicode_ci;', array(array($config['db_name'])));

        $db_rev = get_db_schema();
        $insert = ($db_rev == 0); // if $db_rev == 0, insert the first update

        $updating = 0;
        foreach (get_schema_list() as $file_rev => $file) {
            if ($file_rev > $db_rev) {
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
                    $return = 1;
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
        }
    }

    if (is_a($schemaLock, '\LibreNMS\FileLock')) {
        $schemaLock->release();
    }
}
