<?php

if (!$os) {
    if (preg_match('/^Prestige \d/', $sysDescr)) {
        $os = 'prestige';
    }
    if (preg_match('/^P-.*-/', $sysDescr)) {
        $os = 'prestige';
    }
}
