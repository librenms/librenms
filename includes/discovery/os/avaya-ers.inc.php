<?php

if (str_contains($sysDescr, array('Ethernet Routing Switch', 'ERS-'))) {
    $os = 'avaya-ers';
}
