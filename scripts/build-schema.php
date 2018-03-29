#!/usr/bin/env php
<?php

$install_dir = realpath(__DIR__ . '/..');

if (getenv('DBTEST')) {
    if (!is_file($install_dir . '/config.php')) {
        exec("cp $install_dir/tests/config/config.test.php $install_dir/config.php");
        $create_db = true;
    }
}

$init_modules = array();
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (getenv('DBTEST')) {
    if ($create_db === true) {
        $sql_mode = dbFetchCell("SELECT @@global.sql_mode as sql_mode");
        dbQuery("SET NAMES 'utf8'");
        dbQuery("SET CHARACTER SET 'utf8'");
        dbQuery("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
        dbQuery("SET GLOBAL sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
        dbQuery("USE " . $config['db_name']);
        $build_base = $config['install_dir'] . '/build-base.php';
        exec($build_base, $schema);
    }

    sleep(60);//Sleep for 60 seconds to ensure db work has completed
}

$file = $install_dir . '/misc/db_schema.yaml';
$yaml = Symfony\Component\Yaml\Yaml::dump(dump_db_schema(), 3, 2);

if (file_put_contents($file, $yaml)) {
    echo "Updated!\n";
}
