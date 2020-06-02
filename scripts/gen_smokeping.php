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
include realpath(__DIR__ . '/..') . 'config.php';
?>

menu = Top
title = Network Latency Grapher

<?php

$menu=$data="";
foreach (dbFetchRows("SELECT `type` FROM `devices` WHERE `disabled` = 0 AND `type` != '' GROUP BY `type`") as $groups) {
    //Dot and space need to be replaced, since smokeping doesn't accept it at this level
    $menu='+ ' . str_replace(['.', ' '], '_', $groups['type']) . PHP_EOL;
    $menu.='menu = ' . $groups['type'] . PHP_EOL;
    $menu.='title = ' . $groups['type'] . PHP_EOL;
    $data="";

    $arr=dbFetchRows("SELECT `hostname` FROM `devices` WHERE `type` = ? AND `ignore` = 0 AND `disabled` = 0 order by hostname", array($groups['type']));
    $t_arr=array();
    foreach($arr as $n => $v) {
        $t_arr[$n]=$v['hostname'];
    }
    natsort($t_arr);
    foreach ($t_arr as $n => $device) {
        $data.='++ ' . str_replace(['.', ' '], '_', $device) . PHP_EOL;
        $data.='menu = ' . $device . PHP_EOL;
        $data.='title = ' . $device . PHP_EOL . PHP_EOL;
        if($config['smokeping']['use_folders'] === true) {
            if(preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $device)) {
                $ip=explode(".", $device);
                $folder=$ip[0] . '.' . $ip[1] . '.' . $ip[2];
                $data.='+++ ' . str_replace(".","_",$folder) . PHP_EOL;
                $data.='menu = ' . $folder . PHP_EOL;
                $data.='title = ' . $folder . PHP_EOL . PHP_EOL;
                $data.='++++ ' . str_replace(['.', ' '], '_', $device) . PHP_EOL;
            }
            $data.='menu = ' . $device . PHP_EOL;
            $data.='title = ' . $device . PHP_EOL;
        }
        $data.='host = ' . $device . PHP_EOL . PHP_EOL;
    }
    if(!empty($data)) echo $menu.$data;
}