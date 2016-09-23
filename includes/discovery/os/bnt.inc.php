<?php

if (str_contains($sysDescr, 'Blade Network Technologies', true)) {
    $os = 'bnt';
} elseif (starts_with($sysDescr, 'BNT ')) {
    $os = 'bnt';
}
