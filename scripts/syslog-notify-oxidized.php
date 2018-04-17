#!/usr/bin/env php
<?php

$init_modules = [];
require __DIR__ . '/../includes/init.php';

function oxidized_node_update($hostname, $msg, $username = 'not_provided')
{
    global $config;

    // Work around https://github.com/rack/rack/issues/337
    $msg = str_replace("%", "", $msg);
    $postdata = ["user" => $username, "msg" => $msg];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postdata));
    curl_setopt($curl, CURLOPT_USERAGENT, 'librenms');
    curl_setopt($curl, CURLOPT_URL, $config['oxidized']['url'].'/node/next/'.$hostname);
    curl_exec($curl);
}//end oxidized_node_update()

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
}
