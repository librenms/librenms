<?php

// MYSQL Check - FIXME
// 1 UNKNOWN
include 'config.php';

if (!isset($sql_file)) {
    $sql_file = 'build.sql';
}

$sql_fh = fopen($sql_file, 'r');
if ($sql_fh === false) {
    echo 'ERROR: Cannot open SQL build script '.$sql_file."\n";
    exit(1);
}

$database_link = mysqli_connect($config['db_host'], $config['db_user'], $config['db_pass']);
if ($database_link === false) {
    echo 'ERROR: Cannot connect to database: '.mysqli_error($database_link)."\n";
    exit(1);
}

$select = mysqli_select_db($database_link, $config['db_name']);
if ($select === false) {
    echo 'ERROR: Cannot select database: '.mysqli_error($database_link)."\n";
    exit(1);
}

while (!feof($sql_fh)) {
    $line = fgetss($sql_fh);
    if (!empty($line)) {
        $creation = mysqli_query($database_link, $line);
        if (!$creation) {
            echo 'WARNING: Cannot execute query ('.$line.'): '.mysqli_error($database_link)."\n";
        }
    }
}

fclose($sql_fh);

require 'includes/sql-schema/update.php';
