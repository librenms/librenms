<?php

//SNMPv2-MIB::sysDescr.0 = STRING: Meraki MR18 Cloud Managed AP

if (preg_match('/^Meraki ([A-Z0-9]+) Cloud Managed AP/', $poll_device['sysDescr'], $regexp_result)) {
    $hardware = $regexp_result[1];
}

?>
