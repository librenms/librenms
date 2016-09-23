<?php

if (starts_with($sysObjectId, '.1.3.6.1.4.1.8072.3.2.10') && str_contains($sysDescr, 'Acano')) {
    $os = 'acano';
}
