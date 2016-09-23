<?php

if (str_contains($sysDescr, 'Blade Network Technologies', true)) {
    $os = 'bnt';
}

if (starts_with($sysDescr, 'BNT ')) {
    $os = 'bnt';
}
