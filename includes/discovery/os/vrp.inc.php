<?php

if (!$os) {
    if (stristr($sysDescr, 'VRP (R) Software')) {
        $os = 'vrp';
    }
    if (stristr($sysDescr, 'VRP Software Version')) {
        $os = 'vrp';
    }
    if (stristr($sysDescr, 'Software Version VRP')) {
        $os = 'vrp';
    } if (stristr($sysDescr, 'Versatile Routing Platform Software Version')) {
        $os = 'vrp';
    }
}
