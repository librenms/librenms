<?php

if (str_contains($sysDescr, array('Ethernet Routing Switch', 'ERS-')) && !starts_with($sysObjectId, '.1.3.6.1.4.1.674.10895.3000')) {
    $os = 'avaya-ers';
}
