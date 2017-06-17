#!/usr/bin/env php
<?php

$init_modules = array();
require __DIR__ . '/../includes/init.php';

function oxidized_node_update($hostname, $username, $msg)
{
    global $config;

    // Work around https://github.com/rack/rack/issues/337
    $msg = str_replace("%", "", $msg);
    $postdata = array("user" => $username, "msg" => $msg);

    $version = `git rev-parse --short HEAD`;
    if ($version != "") {
        $version = "/".$version;
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postdata));
    curl_setopt($curl, CURLOPT_USERAGENT, "librenms".$version);
    curl_setopt($curl, CURLOPT_URL, $config['oxidized']['url'].'/node/next/'.$hostname);
    $result = curl_exec($curl);
}//end oxidized_node_update()

$hostname = $argv[1];
$os = $argv[2];
$msg = $argv[3];

if (preg_match('/(SYS-(SW[0-9]+-)?5-CONFIG_I|VSHD-5-VSHD_SYSLOG_CONFIG_I): Configured from .+ by (?P<user>.+) on .*/', $msg, $matches)) {
    $username = $matches['user'];
    oxidized_node_update($hostname, $username, $msg);
} elseif (preg_match('/GBL-CONFIG-6-DB_COMMIT : Configuration committed by user \\\\\'(?P<user>.+)\\\\\'..*/', $msg, $matches)) {
    $username = $matches['user'];
    oxidized_node_update($hostname, $username, $msg);
}
