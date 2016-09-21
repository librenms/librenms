<?php

if (str_contains('ZyWALL', $sysDescr)) {
    $os = 'zywall';
} elseif (starts_with('.1.3.6.1.4.1.890.1.15', $sysObjectId)) {
    $os = 'zywall';
}
