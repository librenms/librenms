<?php

if (starts_with($sysDescr, array('Prestige '))) {
    $os = 'prestige';
} elseif (preg_match('/^P-.*-/', $sysDescr)) {
    $os = 'prestige';
}
