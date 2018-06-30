#!/usr/bin/env php
<?php
/*
* LibreNMS
*
* Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
* This program is free software: you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation, either version 3 of the License, or (at your
* option) any later version.  Please see LICENSE.txt at the top level of
* the source code distribution for details.
*/

$init_modules = array();
require realpath(__DIR__ . '/..') . '/includes/init.php';

?>

menu = Top
title = Network Latency Grapher

<?php

foreach (dbFetchRows("SELECT `type` FROM `devices` WHERE `ignore` = 0 AND `disabled` = 0 AND `type` != '' GROUP BY `type`") as $groups) {
    echo '+ ' . $groups['type'] . PHP_EOL;
    echo 'menu = ' . $groups['type'] . PHP_EOL;
    echo 'title = ' . $groups['type'] . PHP_EOL;
    foreach (dbFetchRows("SELECT `hostname` FROM `devices` WHERE `type` = ? AND `ignore` = 0 AND `disabled` = 0", array($groups['type'])) as $devices) {
        //Dot needs to be replaced, since smokeping doesn't accept it at this level
        echo '++ ' . str_replace(".", "_", $devices['hostname']) . PHP_EOL;
        echo 'menu = ' . $devices['hostname'] . PHP_EOL;
        echo 'title = ' . $devices['hostname'] . PHP_EOL;
        echo 'host = ' . $devices['hostname'] . PHP_EOL . PHP_EOL;
    }
}
