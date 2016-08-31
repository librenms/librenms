<?php

if (!$os) {
    if ($sysDescr == 'SNMP TME') {
        $os = 'papouch-tme';
    } elseif ($sysDescr == 'TME') {
        $os = 'papouch-tme';
    }
}
