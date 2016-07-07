<?php

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.6027.1.')) {
        $os = 'dnos';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.674.10895.3042')) {
        $os = 'dnos';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.674.10895.3044')) {
        $os = 'dnos';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.674.10895.3054')) {
        $os = 'dnos';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.674.10895.3055')) {
        //Dell N2024P
        $os = 'dnos';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.674.10895.3056')) {
        //Dell N2048P
        $os = 'dnos';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.674.10895.3046')) {
        //Dell N4064F
        $os = 'dnos';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.674.10895.3058')) {
        //Dell N3048P
        $os = 'dnos';
    }
    if (strstr($sysObjectId, '.1.3.6.1.4.1.674.10895.3060')) {
        //Dell N3024P
        $os = 'dnos';
    }
}
