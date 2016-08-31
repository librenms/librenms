<?php

if (!$os) {
    if (strstr($sysDescr, '3Com Switch ')) {
        $os = '3com';
    } elseif (strstr($sysDescr, '3Com SuperStack')) {
        $os = '3com';
    } elseif (strstr($sysDescr, '3Com Baseline')) {
        $os = '3com';
    }
}
