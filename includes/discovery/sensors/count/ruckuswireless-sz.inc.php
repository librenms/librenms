<?php

use App\Models\Sensor;

// AP licenses are reported per node, sum them for the cluster
$apLicenses = SnmpQuery::walk('RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApLicense')->values();

if ($apLicenses) {
    $apLicenseTotal = array_sum($apLicenses);

    app('sensor-discovery')->discover(new Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'count',
        'sensor_oid' => '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.10',
        'sensor_index' => 'licenses.total',
        'sensor_type' => 'ruckuswireless-sz',
        'sensor_descr' => 'AP Licenses: Total',
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_limit' => $apLicenseTotal,
        'sensor_current' => $apLicenseTotal,
    ]));
}

unset($apLicenses, $apLicenseTotal);
