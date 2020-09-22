#!/usr/bin/env php
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

$init_modules = ['polling', 'alerts'];
require __DIR__ . '/includes/init.php';

$options = getopt('l:u:r::');

if (isset($options['l'])) {
    if ($options['l'] == 'pollers') {
        $tbl = new Console_Table();
        $tbl->setHeaders(['ID', 'Poller Name', 'Last Polled', '# Devices', 'Poll Time']);
        foreach (dbFetchRows('SELECT * FROM `pollers`') as $poller) {
            $tbl->addRow([$poller['id'], $poller['poller_name'], $poller['last_polled'], $poller['devices'], $poller['time_taken']]);
        }

        echo $tbl->getTable();
    } elseif ($options['l'] == 'groups') {
        $tbl = new Console_Table();
        $tbl->setHeaders(['ID', 'Group Name', 'Description']);
        foreach (dbFetchRows('SELECT * FROM `poller_groups`') as $groups) {
            $tbl->addRow([$groups['id'], $groups['group_name'], $groups['descr']]);
        }

        echo $tbl->getTable();
    }
} elseif (isset($options['u']) && ! empty($options['u'])) {
    if (is_numeric($options['u'])) {
        $db_column = 'id';
    } else {
        $db_column = 'poller_name';
    }

    if (dbDelete('pollers', "`$db_column` = ?", [$options['u']]) >= 0) {
        echo 'Poller ' . $options['u'] . " has been removed\n";
    }
} elseif (isset($options['r'])) {
    if (dbInsert(['poller_name' => Config::get('distributed_poller_name'), 'last_polled' => '0000-00-00 00:00:00', 'devices' => 0, 'time_taken' => 0], 'pollers') >= 0) {
        echo 'Poller ' . Config::get('distributed_poller_name') . " has been registered\n";
    }
} else {
    echo "-l pollers | groups List registered pollers or poller groups\n";
    echo "-u <id> | <poller name> Unregister a poller\n";
    echo "-r Register this install as a poller\n";
}//end if
