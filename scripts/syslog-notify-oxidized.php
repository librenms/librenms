#!/usr/bin/env php
<?php

$init_modules = [];
require __DIR__ . '/../includes/init.php';

$hostname = $argv[1];
$os = $argv[2];
$msg = $argv[3];

if (preg_match('/(SYS-(SW[0-9]+-)?5-CONFIG_I|VSHD-5-VSHD_SYSLOG_CONFIG_I): Configured from .+ by (?P<user>.+) on .*/', $msg, $matches)) {
    oxidized_node_update($hostname, $msg, $matches['user']);
} elseif (preg_match('/GBL-CONFIG-6-DB_COMMIT : Configuration committed by user \\\\\'(?P<user>.+?)\\\\\'..*/', $msg, $matches)) {
    oxidized_node_update($hostname, $msg, $matches['user']);
} elseif (preg_match('/ASA-(config-)?5-111005: (?P<user>.+) end configuration: OK/', $msg, $matches)) {
    oxidized_node_update($hostname, $msg, $matches['user']);
} elseif (preg_match('/startup-config was changed by (?P<user>.+) from telnet client .*/', $msg, $matches)) {
    oxidized_node_update($hostname, $msg, $matches['user']);
} elseif (preg_match('/HWCM\/4\/CFGCHANGE/', $msg, $matches)) { //Huawei VRP devices CFGCHANGE syslog
    oxidized_node_update($hostname, $msg);
} elseif (preg_match('/UI_COMMIT: User \\\\\'(?P<user>.+?)\\\\\' .*/', $msg, $matches)) {
    oxidized_node_update($hostname, $msg, $matches['user']);
} elseif (preg_match('/IMI.+.Startup-config saved on .+ by (?P<user>.+) via .*/', $msg, $matches)) {
    oxidized_node_update($hostname, $msg, $matches['user']); //Alliedware Plus devices. Requires at least V5.4.8-2.1
} elseif (preg_match('/System configuration saved/', $msg, $matches)) {
    oxidized_node_update($hostname, $msg); //ScreenOS
}
