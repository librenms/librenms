<?php

// Sum the per node AP licenses for the cluster total
if (in_array($sensor['sensor_index'], ['licenses.total', 'licenses.remaining'])) {
    $apLicenses = SnmpQuery::walk('RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApLicense')->values();
    $apLicenseTotal = array_sum($apLicenses);

    if ($sensor['sensor_index'] === 'licenses.total') {
        $sensor_value = $apLicenseTotal;
    } elseif (is_numeric($sensor_value)) {
        // remaining is polled from the consumed oid
        $sensor_value = $apLicenseTotal - $sensor_value;
    }
}

unset($apLicenses, $apLicenseTotal);
