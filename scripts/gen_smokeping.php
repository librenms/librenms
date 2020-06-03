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

function menu_element($f_level, $f_name)
{
    $f_data.=str_repeat('+', $f_level) . ' ' . str_replace(".", "_", $f_name) . PHP_EOL;
    $f_data.='menu = ' . $f_name . PHP_EOL;
    $f_data.='title = ' . $f_name . PHP_EOL . PHP_EOL;
    return $f_data;
}

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
    foreach ($arr as $n => $v) {
        $t_arr[$n]=$v['hostname'];
    }
    natsort($t_arr);
    $prev_folder=$prev_folder2="";
    foreach ($t_arr as $n => $device) {
        if ($config['smokeping']['use_folders'] === true) {
            if (preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $device)) {
                $ip=explode(".", $device);
                $folder=$ip[0] . '.' . $ip[1];
                $folder2=$ip[0] . '.' . $ip[1] . '.' . $ip[2];
                if ($prev_folder != $folder) {
                    $data.=menu_element(2, $folder);
                }
                $prev_folder=$folder;
                if ($prev_folder2 != $folder2) {
                    $data.=menu_element(3, $folder2);
                }
                $prev_folder2=$folder2;
                $data.=menu_element(4, $device);
                $data.='+++++ ' . str_replace(['.', ' '], '_', $device) . PHP_EOL;
            } else {
                $data.=menu_element(2, $device);
                $data.='+++ ' . str_replace(['.', ' '], '_', $device) . PHP_EOL;
            }
        } else {
            $data.='++ ' . str_replace(['.', ' '], '_', $device) . PHP_EOL;
        }
        $data.='menu = ' . $device . PHP_EOL;
        $data.='title = ' . $device . PHP_EOL;
        $data.='host = ' . $device . PHP_EOL . PHP_EOL;
    }
    if (!empty($data)) {
        echo $menu.$data;
    }
}