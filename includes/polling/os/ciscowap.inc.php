<?php

//Linux *-lvl7-dev, Cisco Small Business WAP121 (WAP121-E-K9), Version * Wed Oct 17 00:19:29 EDT 2012";

preg_match('/(.*), Cisco Small Business (.*) \((.*)\)(.*)/', $device['sysDescr'], $matches);

$hardware = $matches['2'];
$version = $matches['3'];
