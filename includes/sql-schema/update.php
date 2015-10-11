<?php

// MYSQL Check - FIXME
// 1 UNKNOWN

/*
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2012, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 */

if (!isset($debug)) {
    // Not called from within discovery, let's load up the necessary stuff.
    include 'includes/defaults.inc.php';
    include 'config.php';
    include 'includes/definitions.inc.php';
    include 'includes/functions.php';

    $options = getopt('d');
    if (isset($options['d'])) {
        $debug = true;
    }
    else {
        $debug = false;
    }
}


/* Copyright (C) 2015 Daniel Preussker, QuxLabs UG <preussker@quxlabs.com>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * SQL Schema Update
 * @author Daniel Preussker
 * @copyright 2015 Daniel Preussker, QuxLabs UG
 * @license GPL
 * @package LibreNMS
 * @subpackage Updates
 */


/**
 * Apply collection of patches.
 * @param mixed &$patches Collection
 * @param mixed $component Component
 * @return null|-1
 */
function apply_patches(&$patches,$component) {
    global $config, $limit;
    echo 'Component: '.$component.PHP_EOL;
    $failed = true;
    if (!is_array($patches)) {
        return;
    }
    foreach ($patches as $k=>$patch) {
        $file = $config['install_dir'].'/sql-schema/'.$component.'/'.$patch.'.sql';
        if (file_exists($file)) {
            echo '    '.($patch-1).' => '.((int) $patch).' ';
            foreach (file($file) as $line) {
                $line = trim($line);
                if (!empty($line) && $line[0] != "#") {
                    if (!apply_line($line)) {
                        echo ' ERROR'.PHP_EOL;
                        $failed = true;
                        break 2;
                    } else {
                        echo '.';
                        $failed = false;
                    }
                }
            }
            if ($failed === false) {
                if ($patch == 1) {
                    dbInsert(array('version'=>$patch,'component'=>$component),'dbSchema');
                } else {
                    dbUpdate(array('version'=>$patch),'dbSchema','component = ?',array($component));
                }
                echo 'OK'.PHP_EOL;
                unset($patches[$k]);
            }
        }
        if (isset($_SESSION['stage']) ) {
            $limit++;
            if ( time()-$_SESSION['last'] > 45 ) {
                $_SESSION['offset'] = $limit;
                $GLOBALS['refresh'] = '<b>Updating Component <code>'.$component.'</code>, please wait..</b><sub>'.date('r').'</sub><script>window.location.href = "install.php?offset='.$limit.'";</script>';
                return -1;
            }
        }
    }
    if ($failed === false) {
        echo 'Sucessfully updated.'.PHP_EOL;
    }
    else {
        echo 'Failed some patches.'.PHP_EOL;
    }
}

/**
 * Apply line
 * @param string $line Patch-Line
 * @return bool
 */
function apply_line($line) {
    global $config, $database_link;
    if ($config['db']['extension'] == 'mysqli') {
        if (!mysqli_query($database_link, $line)) {
            echo mysqli_error($database_link);
            return false;
        }
    }
    else {
        if (!mysql_query($line)) {
            echo mysql_error();
            return false;
        }
    }
    return true;
}

$pool_size = dbFetchCell('SELECT @@innodb_buffer_pool_size');
// The following query is from the excellent mysqltuner.pl by Major Hayden https://raw.githubusercontent.com/major/MySQLTuner-perl/master/mysqltuner.pl
$pool_used = dbFetchCell('SELECT SUM(DATA_LENGTH+INDEX_LENGTH) FROM information_schema.TABLES WHERE TABLE_SCHEMA NOT IN ("information_schema", "performance_schema", "mysql") AND ENGINE = "InnoDB" GROUP BY ENGINE ORDER BY ENGINE ASC');
if ($pool_used > $pool_size) {
    echo 'InnoDB Buffersize too small.'.PHP_EOL;
    echo 'Current size: '.($pool_size / 1024 / 1024).' MiB'.PHP_EOL;
    echo 'Minimum Required: '.($pool_used / 1024 / 1024).' MiB'.PHP_EOL;
    echo 'To ensure integrity, we\'re not going to pull any updates until the buffersize has been adjusted.'.PHP_EOL;
    return;
}

if (!dbFetchCell('select 1 from information_schema.COLUMNS where TABLE_SCHEMA = ? && TABLE_NAME = ? && COLUMN_NAME = ?',array($config['db_name'],'dbSchema','component'))) {
    echo 'Migrating to component based versioning..'.PHP_EOL;
    dbQuery('alter table dbSchema add column `component` varchar(255) not null default ""');
    dbUpdate(array('component'=>'org.librenms.core'),'dbSchema','component=""');
    echo 'Finished.'.PHP_EOL;
}

$apply   = array();
$current = array();
$limit   = 150;

echo 'Caching components..';
foreach (dbFetchRows('SELECT version,component FROM `dbSchema` ORDER BY component') as $data) {
    $current[$data['component']] = $data['version'];
}

$components = array_diff(scandir($config['install_dir'].'/sql-schema'),array('..','.'));
foreach ($components as $component) {
    if (is_dir($config['install_dir'].'/sql-schema/'.$component)) {
        foreach (array_diff(scandir($config['install_dir'].'/sql-schema/'.$component),array('..','.')) as $v) {
            $v = (string) str_replace('.sql','',$v);
            if (empty($current[$component]) || $v > $current[$component]) {
                $apply[$component][] = $v;
            }
        }
        if (!empty($apply[$component])) {
            sort($apply[$component],SORT_NUMERIC);
        }
    }
}
echo '. Done'.PHP_EOL;

foreach ($apply as $component=>$patches) {
    if (!empty($patches)) {
        if (apply_patches($patches,$component) == -1) {
            return;
        }
    }
}
if( isset($_SESSION['stage']) ) {
    $_SESSION['build-ok'] = true;
}
