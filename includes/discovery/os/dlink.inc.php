<?php

if (str_contains($sysDescr, array('D-Link DES-', 'Dlink DES-'))) {
    $os = 'dlink';
} elseif (starts_with($sysDescr, array('DES-', 'DGS-'))) {
    $os = 'dlink';
}
