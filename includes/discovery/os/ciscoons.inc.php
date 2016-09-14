<?php
if (!$os) {
    if (str_contains($sysDescr, 'Cisco ONS')) {
        $os = 'ciscoons';
    }
}