<?php
d_echo("Entering Firebrick IPSec Tunnels");

if ($device['os_group'] == 'firebrick') {
    $ipsec_array = snmpwalk_cache_oid($device, 'fbIPsecConnectionEntry', [], 'FIREBRICK-IPSEC-MIB');
    
}

