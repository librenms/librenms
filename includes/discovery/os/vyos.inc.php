<?php

if (starts_with($sysDescr, 'Vyatta VyOS') || starts_with($sysDescr, 'VyOS', true)) {
    $os = 'vyos';
}
