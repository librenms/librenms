<?php

// SNMPv2-MIB::sysDescr.0  Brocade VDX Switch.
if (preg_match('/Brocade ([\s\d\w]+)/', $poll_device['sysDescr'], $hardware)) {
    $hardware = $hardware[1];
}
