<?php
/**
 * update.php
 *
 * Database update script
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Copyright (C) 2006-2012, Observium Developers - http://www.observium.org
 *
 * @link       https://www.librenms.org
 * @copyright  2017-2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use Database\Seeders\DefaultWidgetSeeder;

if (! isset($init_modules) && php_sapi_name() == 'cli') {
    // Not called from within discovery, let's load up the necessary stuff.
    $init_modules = [];
    require realpath(__DIR__ . '/../..') . '/includes/init.php';
}

$return = 0;

// make sure the cache_locks table exists before attempting to use a db lock
if (config('cache.default') == 'database' && ! \Schema::hasTable('cache_locks')) {
    $skip_schema_lock = true;
}

$schemaLock = Cache::lock('schema', 86000);
if (! empty($skip_schema_lock) || $schemaLock->get()) {
    $db_rev = \LibreNMS\DB\Schema::getLegacySchema();

    $migrate_opts = ['--force' => true, '--ansi' => true];

    if ($db_rev === 0) {
        $migrate_opts['--seed'] = true;
        $return = Artisan::call('migrate', $migrate_opts);
        echo Artisan::output();
    } elseif ($db_rev < 1000) {
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
                                if (! dbQuery($line)) {
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
        $db_rev = \LibreNMS\DB\Schema::getLegacySchema();
    }

    if ($db_rev == 1000) {
        Artisan::call('db:seed', ['--force' => true, '--ansi' => true, '--class' => DefaultWidgetSeeder::class]);
        $return = Artisan::call('migrate', $migrate_opts);
        echo Artisan::output();
    }

    $schemaLock->release();
}
