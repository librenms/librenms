<?php

if (!$os) {
    if (preg_match('/^VMware ESX/', $sysDescr)) {
        $os = 'vmware';
    }
    if (preg_match('/^VMware-vCenter-Server-Appliance/', $sysDescr)) {
        $os = 'vmware';
    }
}
