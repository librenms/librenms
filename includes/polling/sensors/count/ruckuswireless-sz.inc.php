<?php

// Sum the per node AP licenses for the cluster total
if ($sensor['sensor_index'] === 'licenses.total') {
    $apLicenses = SnmpQuery::walk('RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApLicense')->values();
    $sensor_value = array_sum($apLicenses);
}

unset($apLicenses);
