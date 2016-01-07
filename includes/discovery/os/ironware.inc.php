<?php

if (!$os) {
    if (preg_match('/IronWare/', $sysDescr)) {
        $os = 'ironware';
    }
    if (preg_match('/Iron Software Version 07/', $sysDescr)) {
        $os = 'ironware';
    }
}
