#!/usr/bin/env php
<?php

if (!isset($init_modules)) {
    $init_modules = array();
    require __DIR__  . '/includes/init.php';

    $options = getopt('d');
    $debug = isset($options['d']);
}

if (!isset($sql_file)) {
    $sql_file = 'build.sql';
}

$sql_fh = fopen($sql_file, 'r');
if ($sql_fh === false) {
    echo 'ERROR: Cannot open SQL build script '.$sql_file.PHP_EOL;
    exit(1);
}

// only import build.sql to an empty database
$tables = dbFetchRows("SHOW TABLES FROM {$config['db_name']}");
if (empty($tables)) {
    $limit = 0;

    while (!feof($sql_fh)) {
        $line = fgetss($sql_fh);
        if (isset($_SESSION['stage'])) {
            $limit++;
            if (isset($_SESSION['offset']) && $limit < $_REQUEST['offset']) {
                continue;
            } elseif (time()-$_SESSION['last'] > 45) {
                $_SESSION['offset'] = $limit;
                $GLOBALS['refresh'] = '<b>Installing, please wait..</b><sub>'.date('r').'</sub><script>window.location.href = "install.php?offset='.$limit.'";</script>';
                return;
            } else {
                echo 'Step #'.$limit.' ...'.PHP_EOL;
            }
        }

        if (!empty($line)) {
            $creation = dbQuery($line);
            if (!$creation) {
                echo 'WARNING: Cannot execute query ('.$line.'): '.mysqli_error($database_link)."\n";
            }
        }
    }
}

fclose($sql_fh);

require 'includes/sql-schema/update.php';
