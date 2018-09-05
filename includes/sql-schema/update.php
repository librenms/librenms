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
    $init_modules = ['laravel'];
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

    $db_rev = get_db_schema();

    $migrate_opts = ['--force' => true];
    if(\Config::has('database.connections.setup')) {
        $migrate_opts['--database'] = 'setup';
    }

    if ($db_rev === 0) {
        $migrate_opts['--seed'] = true;
        $return = \Artisan::call('migrate', $migrate_opts);
    } elseif ($db_rev == 1000) {
        $return = \Artisan::call('migrate', $migrate_opts);
    } else {
        // legacy update
        d_echo("DB Schema update started....\n");

        // Set Database Character set and Collation
        dbQuery('ALTER DATABASE CHARACTER SET utf8 COLLATE utf8_unicode_ci;');

        echo "-- Updating database schema\n";
        foreach (get_schema_list() as $file_rev => $file) {
            if ($file_rev > $db_rev) {
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

                if ($db_rev == 0) {
                    dbInsert(['version' => $file_rev], 'dbSchema');
                } else {
                    dbUpdate(['version' => $file_rev], 'dbSchema');
                }
                $db_rev = $file_rev;
            }//end if
        }//end foreach

        echo "-- Done\n";
        // end legacy update
    }

    if (isset($schemaLock)) {
        $schemaLock->release();
    }
} catch (LockException $e) {
    echo $e->getMessage() . PHP_EOL;
    $return = 1;
}
