<?php

if (str_contains($sysDescr, 'Blade Network Technologies', true)) {
    $os = 'bnt';
}

if (starts_with('BNT ', $sysDescr)) {
    $os = 'bnt';
}
