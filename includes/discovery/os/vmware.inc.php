<?php

if (starts_with($sysDescr, array('VMware ESX', 'VMware-vCenter-Server-Appliance'))) {
    $os = 'vmware';
}
