<?php

use App\Models\Sensor;
use LibreNMS\Enum\SensorCategory;

// AP licenses are reported per node, sum them for the cluster
$apLicenses = SnmpQuery::walk('RUCKUS-CTRL-MIB::ruckusCtrlSystemNodeNumApLicense')->values();
$apConsumed = SnmpQuery::get('RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0')->value();

if ($apLicenses) {
    $apLicenseTotal = array_sum($apLicenses);

    app('sensor-discovery')->discover(new Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'count',
        'sensor_oid' => '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.10',
        'sensor_index' => 'licenses.total',
        'sensor_type' => 'ruckuswireless-sz',
        'sensor_descr' => 'AP Licenses: Total',
        'category' => SensorCategory::License,
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_current' => $apLicenseTotal,
    ]));

    if (is_numeric($apConsumed)) {
        // every connected AP consumes a license, APs above the total do not serve WiFi
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'count',
            'sensor_oid' => '.1.3.6.1.4.1.25053.1.4.1.1.1.15.1.0',
            'sensor_index' => 'licenses.consumed',
            'sensor_type' => 'ruckuswireless-sz',
            'sensor_descr' => 'AP Licenses: Consumed',
            'category' => SensorCategory::License,
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_limit' => $apLicenseTotal,
            'sensor_current' => $apConsumed,
        ]));

        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'count',
            'sensor_oid' => '.1.3.6.1.4.1.25053.1.4.1.1.1.15.1.0',
            'sensor_index' => 'licenses.remaining',
            'sensor_type' => 'ruckuswireless-sz',
            'sensor_descr' => 'AP Licenses: Remaining',
            'category' => SensorCategory::License,
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_limit_low' => 0,
            'sensor_current' => $apLicenseTotal - $apConsumed,
        ]));
    }
}

unset($apLicenses, $apLicenseTotal, $apConsumed);
