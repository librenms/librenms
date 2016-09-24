<?php

if (str_contains($sysDescr, 'IES-') && !str_contains($sysDescr, 'Cisco Systems')) {
    $os = 'ies';
}
