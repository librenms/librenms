<?php

// Synology DSM
if (starts_with($sysDescr, 'Linux')) {
    $init_params = array(
        'syno_hw_version',
        'syno_dyn_module',
    );

    if (str_contains(snmp_get($device, 'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0', '-Osqnv'), $init_params)) {
        $os = 'dsm';
    }
}
