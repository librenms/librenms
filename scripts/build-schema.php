#!/usr/bin/env php
<?php

$install_dir = realpath(__DIR__ . '/..');

// update.php will call init.php
require $install_dir . '/includes/sql-schema/update.php';

// Schema dump for speeding up initial migrations
Artisan::call('schema:dump', ['--path' => database_path('schema/mysql-schema.dump')]);

$file = $install_dir . '/misc/db_schema.yaml';
$yaml = Symfony\Component\Yaml\Yaml::dump(dump_db_schema(), 3, 2);

if (file_put_contents($file, $yaml)) {
    echo "Updated!\n";
}
