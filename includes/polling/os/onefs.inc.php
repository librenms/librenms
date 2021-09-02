<?php

//sysDescr Isilon OneFS HPCDATA-1 v7.0.2.8 Isilon OneFS v7.0.2.8 B_7_0_2_8_269(RELEASE) amd64

preg_match('/Isilon OneFS HPCDATA-1 (.*) Isilon OneFS (.*) (.*)\(RELEASE\) (.*)/', $device['sysDescr'], $matches);
$version = $matches[2];
$hardware = $matches[3];
