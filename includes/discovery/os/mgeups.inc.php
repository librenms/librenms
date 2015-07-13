<?php

if (!$os) {
    if (strstr($sysDescr, 'Pulsar M')) {
        $os = 'mgeups';
    }
    if (preg_match('/^Galaxy /', $sysDescr)) {
        $os = 'mgeups';
    }
    if (preg_match('/^Evolution /', $sysDescr)) {
        $os = 'mgeups';
    }
    if ($sysDescr == 'MGE UPS SYSTEMS - Network Management Proxy') {
        $os = 'mgeups';
    }
}
