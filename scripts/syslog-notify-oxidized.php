#!/usr/bin/env php
<?php

$init_modules = [];
require __DIR__ . '/../includes/init.php';

$hostname = $argv[1];
$os = $argv[2];
$msg = $argv[3];

$oxidized_api = new \App\ApiClients\Oxidized();
if (preg_match('/Configured from .+ by (?P<user>\S+)/', $msg, $matches)) {
    $oxidized_api->updateNode($hostname, $msg, $matches['user']);
} elseif (preg_match('/Configuration committed by user \\\\\'(?P<user>.+?)\\\\\'/', $msg, $matches)) {
    $oxidized_api->updateNode($hostname, $msg, $matches['user']);
} elseif (preg_match('/ASA-(config-)?5-111005: (?P<user>\S+) end configuration: OK/', $msg, $matches)) {
    $oxidized_api->updateNode($hostname, $msg, $matches['user']);
} elseif (preg_match('/startup-config was changed by (?P<user>\S+)/', $msg, $matches)) {
    $oxidized_api->updateNode($hostname, $msg, $matches['user']);
} elseif (preg_match('/UI_COMMIT: User \\\\\'(?P<user>.+?)\\\\\'/', $msg, $matches)) {
    $oxidized_api->updateNode($hostname, $msg, $matches['user']);
} elseif (preg_match('/IMI.+.Startup-config saved on .+ by (?P<user>\S+)/', $msg, $matches)) {
    $oxidized_api->updateNode($hostname, $msg, $matches['user']); //Alliedware Plus devices. Requires at least V5.4.8-2.1
} elseif (isset($hostname, $msg)) {
    // without user detection...
    $oxidized_api->updateNode($hostname, $msg);
}
