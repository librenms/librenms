<?php

if (str_contains($sysDescr, array('Pulsar M', 'MGE UPS SYSTEMS - Network Management Proxy'))) {
    $os = 'mgeups';
}

if (starts_with($sysDescr, array('Galaxy ', 'Evolution ', 'Comet'))) {
    $os = 'mgeups';
}
