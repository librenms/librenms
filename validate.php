#!/usr/bin/php
<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$options = getopt('m:h::');

if (isset($options['h'])) {
    echo  
        "\n Validate setup tool

    Usage: ./validate.php [-m <module>] [-h]
        -h This help section.
        -m Any sub modules you want to run, comma separated:
          - mail: this will test your email settings  (uses default_mail option even if default_only is not set).
          - dist-poller: this will test for the install running as a distributed poller.

        Example: ./validate.php -m mail.

        "
    ;
    exit;
}

if (strstr(`php -ln config.php`, 'No syntax errors detected')) {
    $first_line = `head -n1 config.php`;
    $last_lines = explode(PHP_EOL, `tail -n2 config.php`);
    if (strstr($first_line, '\<\?php')) {
        print_fail("config.php doesn't start with a <?php - please fix this ($first_line)");
    }
    else if ($last_lines[0] == '?>' && $last_lines[1] == '') {
        print_fail('config.php contains a new line at the end, please remove any whitespace at the end of the file and also remove ?>');
    }
    else if ($last_lines[1] == '?>') {
        print_warn("It looks like you have ?> at the end of config.php, it's suggested you remove this");
    }
    else {
        print_ok('config.php tested ok');
    }
}
else {
    print_fail('Syntax error in config.php');
}

// load config.php now
require_once 'includes/defaults.inc.php';
require_once 'config.php';
require_once 'includes/definitions.inc.php';
require_once 'includes/functions.php';
require_once $config['install_dir'].'/includes/alerts.inc.php';

// Let's test the user configured if we have it
if (isset($config['user'])) {
    $tmp_user = $config['user'];
    $tmp_dir = $config['install_dir'];
    $tmp_log = $config['log_dir'];
    $find_result = rtrim(`find $tmp_dir \! -user $tmp_user -not -path $tmp_log`);
    if (!empty($find_result)) {
        // This isn't just the log directory, let's print the list to the user
        $files = explode(PHP_EOL, $find_result);
        if (is_array($files)) {
            print_fail("We have found some files that are owned by a different user than $tmp_user, this will stop you updating automatically and / or rrd files being updated causing graphs to fail:\n");
            foreach ($files as $file) {
                echo "$file\n";
            }
            echo "\n";
        }
    }
}

// Run test on MySQL
$test_db = @mysqli_connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if (mysqli_connect_error()) {
    print_fail('Error connecting to your database '.mysqli_connect_error());
}
else {
    print_ok('Database connection successful');
}

// Test for MySQL Strict mode
$strict_mode = dbFetchCell("SELECT @@global.sql_mode");
if(strstr($strict_mode, 'STRICT_TRANS_TABLES')) {
    print_warn('You have MySQL STRICT_TRANS_TABLES enabled, it is advisable to disable this until full support has been added: https://dev.mysql.com/doc/refman/5.0/en/sql-mode.html');
}

// Test for MySQL InnoDB buffer size
$innodb_buffer = innodb_buffer_check();
if ($innodb_buffer['used'] > $innodb_buffer['size']) {
    print_fail('Your Innodb buffer size is not big enough...');
    echo warn_innodb_buffer($innodb_buffer);
}

// Test transports
if ($config['alerts']['email']['enable'] === true) {
    print_warn('You have the old alerting system enabled - this is to be deprecated on the 1st of June 2015: https://groups.google.com/forum/#!topic/librenms-project/1llxos4m0p4');
}

// Test rrdcached
if (!$config['rrdcached']) {
    $rrd_dir = stat($config['rrd_dir']);
    if ($rrd_dir[4] == 0 || $rrd_dir[5] == 0) {
        print_warn('Your RRD directory is owned by root, please consider changing over to user a non-root user');
    }

    if (substr(sprintf('%o', fileperms($config['rrd_dir'])), -3) != 775) {
        print_warn('Your RRD directory is not set to 0775, please check our installation instructions');
    }
}

// Disk space and permission checks
if (substr(sprintf('%o', fileperms($config['temp_dir'])), -3) != 777) {
    print_warn('Your tmp directory ('.$config['temp_dir'].") is not set to 777 so graphs most likely won't be generated");
}

$space_check = (disk_free_space($config['install_dir']) / 1024 / 1024);
if ($space_check < 512 && $space_check > 1) {
    print_warn('Disk space where '.$config['install_dir'].' is located is less than 512Mb');
}

if ($space_check < 1) {
    print_fail('Disk space where '.$config['install_dir'].' is located is empty!!!');
}

// Check programs
$bins = array('fping');
foreach ($bins as $bin) {
    if (!is_file($config[$bin])) {
        print_fail("$bin location is incorrect or bin not installed");
    }
    else {
        print_ok("$bin has been found");
    }
}

// Modules test
$modules = explode(',', $options['m']);
foreach ($modules as $module) {
    switch ($module) {
    case 'mail':
        if ($config['alert']['transports']['mail'] === true) {
            $run_test = 1;
            if (empty($config['alert']['default_mail'])) {
                print_fail('default_mail config option needs to be specified to test email');
                $run_test = 0;
            }
            else if ($config['email_backend'] == 'sendmail') {
                if (empty($config['email_sendmail_path']) || !file_exists($config['email_sendmail_path'])) {
                    print_fail("You have selected sendmail but not configured email_sendmail_path or it's not valid");
                    $run_test = 0;
                }
            }
            else if ($config['email_backend'] == 'smtp') {
                if (empty($config['email_smtp_host'])) {
                    print_fail('You have selected smtp but not configured an smtp host');
                    $run_test = 0;
                }

                if (empty($config['email_smtp_port'])) {
                    print_fail('You have selected smtp but not configured an smtp port');
                    $run_test = 0;
                }

                if (($config['email_smtp_auth'] === true) && (empty($config['email_smtp_username']) || empty($config['email_smtp_password']))) {
                    print_fail('You have selected smtp but not configured a username or password');
                    $run_test = 0;
                }
            }//end if
            if ($run_test == 1) {
                if ($err = send_mail($config['alert']['default_mail'], 'Test email', 'Testing email from NMS')) {
                    print_ok('Email has been sent');
                }
                else {
                    print_fail('Issue sending email to '.$config['alert']['default_mail'].' with error '.$err);
                }
            }
        }//end if
        break;
    case 'dist-poller':
        if ($config['distributed_poller'] !== true) {
            print_fail('You have not enabled distributed_poller');
        }
        else {
            if (empty($config['distributed_poller_memcached_host'])) {
                print_fail('You have not configured $config[\'distributed_poller_memcached_host\']');
            }
            elseif (empty($config['distributed_poller_memcached_port'])) {
                print_fail('You have not configured $config[\'distributed_poller_memcached_port\']');
            }
            else {
                $connection = @fsockopen($config['distributed_poller_memcached_host'], $config['distributed_poller_memcached_port']);
                if (!is_resource($connection)) {
                    print_fail('We could not get memcached stats, it is possible that we cannot connect to your memcached server, please check');
                }
                else {
                    fclose($connection);
                    print_ok('Connection to memcached is ok');
                }
            }
            if (empty($config['rrdcached'])) {
                print_fail('You have not configured $config[\'rrdcached\']'); 
            }
            elseif (empty($config['rrd_dir'])) {
                print_fail('You have not configured $config[\'rrd_dir\']');
            }
            else {
                list($host,$port) = explode(':',$config['rrdcached']);
                $connection = @fsockopen($host, $port);
                if (is_resource($connection)) {
                    fclose($connection);
                    print_ok('Connection to rrdcached is ok');
                }
                else {
                    print_fail('Cannot connect to rrdcached instance');
                }
            }
        }
        break;
    }//end switch
}//end foreach

// End


function print_ok($msg) {
    echo "[OK]      $msg\n";

}//end print_ok()


function print_fail($msg) {
    echo "[FAIL]    $msg\n";

}//end print_fail()


function print_warn($msg) {
    echo "[WARN]    $msg\n";

}//end print_warn()
