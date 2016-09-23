<?php

if (starts_with($sysDescr, array('Prestige '))) {
    $os = 'prestige';
}

if (preg_match('/^P-.*-/', $sysDescr)) {
    $os = 'prestige';
}
