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

use LibreNMS\Config;
use LibreNMS\Exceptions\DatabaseConnectException;
use LibreNMS\Exceptions\LockException;
use LibreNMS\Util\FileLock;
use LibreNMS\Util\MemcacheLock;

if (!isset($init_modules) && php_sapi_name() == 'cli') {
    // Not called from within discovery, let's load up the necessary stuff.
    $init_modules = [];
    require realpath(__DIR__ . '/../..') . '/includes/init.php';
}

$return = 0;

try {
    if (isset($skip_schema_lock) && !$skip_schema_lock) {
        if (Config::get('distributed_poller')) {
            $schemaLock = MemcacheLock::lock('schema', 30, 86000);
        } else {
            $schemaLock = FileLock::lock('schema', 30);
        }
    }

    // only import build.sql to an empty database
    $tables = dbFetchRows("SHOW TABLES");

    if (empty($tables)) {
        echo "-- Creating base database structure\n";
        $step = 0;
        $sql_fh = fopen('build.sql', 'r');
        if ($sql_fh === false) {
            echo 'ERROR: Cannot open SQL build script build.sql' . PHP_EOL;
            $return = 1;
        }

        while (!feof($sql_fh)) {
            $line = fgetss($sql_fh);
            echo 'Step #' . $step++ . ' ...' . PHP_EOL;

            if (!empty($line)) {
                if (!dbQuery($line)) {
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
        dbQuery('ALTER DATABASE CHARACTER SET utf8 COLLATE utf8_unicode_ci;');

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
                if (($data = file_get_contents($file)) !== false) {
                    foreach (explode("\n", $data) as $line) {
                        if (trim($line)) {
                            d_echo("$line \n");

                            if ($line[0] != '#') {
                                if (!dbQuery($line)) {
                                    $return = 2;
                                    $err++;
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

    if (isset($schemaLock)) {
        $schemaLock->release();
    }
} catch (LockException $e) {
    echo $e->getMessage() . PHP_EOL;
    $return = 1;
}
