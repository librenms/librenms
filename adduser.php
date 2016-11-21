#!/usr/bin/env php
<?php

/*
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage cli
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

$init_modules = array();
if (php_sapi_name() != 'cli') {
    $init_modules[] = 'auth';
}
require __DIR__ . '/includes/init.php';

if (auth_usermanagement()) {
    if (isset($argv[1]) && isset($argv[2]) && isset($argv[3])) {
        if (!user_exists($argv[1])) {
            if (adduser($argv[1], $argv[2], $argv[3], @$argv[4])) {
                echo 'User '.$argv[1]." added successfully\n";
            }
        } else {
            echo 'User '.$argv[1]." already exists!\n";
        }
    } else {
        echo "Add User Tool\nUsage: ./adduser.php <username> <password> <level 1-10> [email]\n";
    }
} else {
    echo "Auth module does not allow adding users!\n";
}//end if
