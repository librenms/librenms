<?php

if (starts_with($sysDescr, 'Vyatta') && !str_contains($sysDescr, 'VyOS')) {
    $os = 'vyatta';
}
