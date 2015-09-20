<?php

if (!$os) {
    if (stristr($sysDescr, 'VRP (R) Software') || stristr($sysDescr, 'VRP Software Version') || stristr($sysDescr, 'Software Version VRP') || stristr($sysDescr, 'Versatile Routing Platform Software')) {
        $os = 'vrp';
    }
}
