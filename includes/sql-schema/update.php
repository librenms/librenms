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
    if (isset($options['d'])) {
        $debug = true;
    } else {
        $debug = false;
    }
}

$insert = 0;

if ($db_rev = @dbFetchCell('SELECT version FROM `dbSchema` ORDER BY version DESC LIMIT 1')) {
} else {
    $db_rev = 0;
    $insert = 1;
}

$updating = 0;

$include_dir_regexp = '/\.sql$/';

if ($handle = opendir($config['install_dir'].'/sql-schema')) {
    while (false !== ($file = readdir($handle))) {
        if (filetype($config['install_dir'].'/sql-schema/'.$file) == 'file' && preg_match($include_dir_regexp, $file)) {
            $filelist[] = $file;
        }
    }

    closedir($handle);
}

asort($filelist);
$tmp = explode('.', max($filelist), 2);
if ($tmp[0] <= $db_rev) {
    if ($debug) {
        echo "DB Schema already up to date.\n";
    }
    return;
}

// Set Database Character set and Collation
dbQuery('ALTER DATABASE ? CHARACTER SET utf8 COLLATE utf8_unicode_ci;', array(array($config['db_name'])));

$limit = 150; //magic marker far enough in the future
foreach ($filelist as $file) {
    list($filename,$extension) = explode('.', $file, 2);
    if ($filename > $db_rev) {
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

        echo sprintf('%03d', $db_rev).' -> '.sprintf('%03d', $filename).' ...';

        $err = 0;

        if ($fd = @fopen($config['install_dir'].'/sql-schema/'.$file, 'r')) {
            $data = fread($fd, 4096);
            while (!feof($fd)) {
                $data .= fread($fd, 4096);
            }

            foreach (explode("\n", $data) as $line) {
                if (trim($line)) {
                    d_echo("$line \n");

                    if ($line[0] != '#') {
                        $update = mysqli_query($database_link, $line);
                        if (!$update) {
                            $err++;
                            if ($debug) {
                                echo mysqli_error($database_link)."\n";
                            }
                        }
                    }
                }
            }

            echo " done ($err errors).\n";
        } else {
            echo " Could not open file!\n";
        }//end if

        $updating++;
        $db_rev = $filename;
        if ($insert) {
            dbInsert(array('version' => $db_rev), 'dbSchema');
            $insert = 0;
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
