<?php

// sysDescr.0 = STRING: Hardware: x86 Family 6 Model 1 Stepping 9 AT/AT COMPATIBLE  - Software: Windows NT Version 4.0  (Build Number: 1381 Multiprocessor Free )
// sysDescr.0 = STRING: Hardware: x86 Family 6 Model 3 Stepping 4 AT/AT COMPATIBLE  - Software: Windows NT Version 3.51  (Build Number: 1057 Multiprocessor Free )
// sysDescr.0 = STRING: Hardware: x86 Family 16 Model 4 Stepping 2 AT/AT COMPATIBLE - Software: Windows 2000 Version 5.1 (Build 2600 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: x86 Family 15 Model 2 Stepping 5 AT/AT COMPATIBLE - Software: Windows 2000 Version 5.0 (Build 2195 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: AMD64 Family 16 Model 2 Stepping 3 AT/AT COMPATIBLE - Software: Windows Version 6.0 (Build 6002 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: EM64T Family 6 Model 26 Stepping 5 AT/AT COMPATIBLE - Software: Windows Version 5.2 (Build 3790 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: Intel64 Family 6 Model 23 Stepping 6 AT/AT COMPATIBLE - Software: Windows Version 6.1 (Build 7600 Multiprocessor Free)
// sysDescr.0 = STRING: Hardware: AMD64 Family 16 Model 8 Stepping 0 AT/AT COMPATIBLE - Software: Windows Version 6.1 (Build 7600 Multiprocessor Free)

if (str_contains($device['sysDescr'], 'AMD64')) {
    $hardware = 'AMD x64';
} elseif (str_contains($device['sysDescr'], array('EM64', 'Intel64'))) {
    $hardware = 'Intel x64';
} elseif (str_contains($device['sysDescr'], 'x86')) {
    $hardware = 'Generic x86';
} elseif (str_contains($device['sysDescr'], 'ia64')) {
    $hardware = 'Intel Itanium IA64';
}

if ($device['sysObjectID'] == '.1.3.6.1.4.1.311.1.1.3.1.1') {
    // Client
    if (str_contains($device['sysDescr'], 'Build 14393')) {
        $version = '10 AU (NT 6.3)';
    } elseif (str_contains($device['sysDescr'], 'Build 10586')) {
        $version = '10 U1 (NT 6.3)';
    } elseif (str_contains($device['sysDescr'], 'Build 10240')) {
        $version = '10 (NT 6.3)';
    } elseif (str_contains($device['sysDescr'], 'Build 9600')) {
        $version = '8.1 U1 (NT 6.3)';
    } elseif (str_contains($device['sysDescr'], 'Version 6.3 (Build 9200')) {
        $version = '8.1 (NT 6.3)';
    } elseif (str_contains($device['sysDescr'], 'Build 9200')) {
        $version = '8 (NT 6.2)';
    } elseif (str_contains($device['sysDescr'], 'Build 7601')) {
        $version = '7 SP1 (NT 6.1)';
    } elseif (str_contains($device['sysDescr'], 'Build 7600')) {
        $version = '7 (NT 6.1)';
    } elseif (str_contains($device['sysDescr'], 'Build 6002')) {
        $version = 'Vista SP2 (NT 6.0)';
    } elseif (str_contains($device['sysDescr'], 'Build 6001')) {
        $version = 'Vista SP1 (NT 6.0)';
    } elseif (str_contains($device['sysDescr'], 'Build 6000')) {
        $version = 'Vista (NT 6.0)';
    } elseif (str_contains($device['sysDescr'], 'Build 3790')) {
        $version = 'XP x64 (NT 5.2)';
    } elseif (str_contains($device['sysDescr'], 'Build 2600')) {
        $version = 'XP (NT 5.1)';
    } elseif (str_contains($device['sysDescr'], 'Build 2195')) {
        $version = '2000 (NT 5.0)';
    } elseif (str_contains($device['sysDescr'], 'Build Number: 1381')) {
        $version = 'NT 4.0 Workstation';
    } elseif (str_contains($device['sysDescr'], 'Build Number: 1057')) {
        $version = 'NT 3.51 Workstation';
    }
} elseif ($device['sysObjectID'] == '.1.3.6.1.4.1.311.1.1.3.1.2') {
    // Server
    if (str_contains($device['sysDescr'], 'Build 14393')) {
        $version = 'Server 2016 (NT 6.3)';
    } elseif (str_contains($device['sysDescr'], 'Build 9600')) {
        $version = 'Server 2012 R2 (NT 6.3)';
    } elseif (str_contains($device['sysDescr'], 'Build 9200')) {
        $version = 'Server 2012 (NT 6.2)';
    } elseif (str_contains($device['sysDescr'], 'Build 7601')) {
        $version = 'Server 2008 R2 SP1 (NT 6.1)';
    } elseif (str_contains($device['sysDescr'], 'Build 7600')) {
        $version = 'Server 2008 R2 (NT 6.1)';
    } elseif (str_contains($device['sysDescr'], 'Build 6002')) {
        $version = 'Server 2008 SP2 (NT 6.0)';
    } elseif (str_contains($device['sysDescr'], 'Build 6001')) {
        $version = 'Server 2008 (NT 6.0)';
    } elseif (str_contains($device['sysDescr'], 'Build 3790')) {
        $version = 'Server 2003 (NT 5.2)';
    } elseif (str_contains($device['sysDescr'], 'Build 2195')) {
        $version = '2000 Server (NT 5.0)';
    } elseif (str_contains($device['sysDescr'], 'Build Number: 1381')) {
        $version = 'NT Server 4.0';
    } elseif (str_contains($device['sysDescr'], 'Build Number: 1057')) {
        $version = 'NT Server 3.51';
    }
} elseif ($device['sysObjectID'] == '.1.3.6.1.4.1.311.1.1.3.1.3') {
    // Datacenter
    if (str_contains($device['sysDescr'], 'Build 14393')) {
        $version = 'Server 2016 Datacenter (NT 6.3)';
    } elseif (str_contains($device['sysDescr'], 'Build 9600')) {
        $version = 'Server 2012 R2 Datacenter (NT 6.3)';
    } elseif (str_contains($device['sysDescr'], 'Build 9200')) {
        $version = 'Server 2012 Datacenter (NT 6.2)';
    } elseif (str_contains($device['sysDescr'], 'Build 7601')) {
        $version = 'Server 2008 Datacenter R2 SP1 (NT 6.1)';
    } elseif (str_contains($device['sysDescr'], 'Build 7600')) {
        $version = 'Server 2008 Datacenter R2 (NT 6.1)';
    } elseif (str_contains($device['sysDescr'], 'Build 6002')) {
        $version = 'Server 2008 Datacenter SP2 (NT 6.0)';
    } elseif (str_contains($device['sysDescr'], 'Build 6001')) {
        $version = 'Server 2008 Datacenter (NT 6.0)';
    } elseif (str_contains($device['sysDescr'], 'Build 3790')) {
        $version = 'Server 2003 Datacenter (NT 5.2)';
    } elseif (str_contains($device['sysDescr'], 'Build 2195')) {
        $version = '2000 Datacenter Server (NT 5.0)';
    } elseif (str_contains($device['sysDescr'], 'Build Number: 1381')) {
        $version = 'NT Datacenter 4.0';
    } elseif (str_contains($device['sysDescr'], 'Build Number: 1057')) {
        $version = 'NT Datacenter 3.51';
    }
}//end version if

if (str_contains($device['sysDescr'], 'Multiprocessor')) {
    $features = 'Multiprocessor';
} elseif (str_contains($device['sysDescr'], 'Uniprocessor')) {
    $features = 'Uniprocessor';
}

// Detect Dell hardware via OpenManage SNMP
$hw = snmp_get($device, '.1.3.6.1.4.1.674.10892.1.300.10.1.9.1', '-Oqv', 'MIB-Dell-10892');
$hw = trim(str_replace('"', '', $hw));
if (!empty($hw)) {
    $hardware = 'Dell ' . $hw;

    $serial = snmp_get($device, '.1.3.6.1.4.1.674.10892.1.300.10.1.11.1', '-Oqv', 'MIB-Dell-10892');
    $serial = trim(str_replace('"', '', $serial));
} else {
    $hw = trim(snmp_get($device, 'cpqSiProductName.0', '-Oqv', 'CPQSINFO-MIB', 'hp'), '"');
    if (!empty($hw)) {
        $hardware = $hw;
    }
}
