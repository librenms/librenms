<?php

/**
 * pmp.inc.php
 *
 * LibreNMS state discovery module for Cambium PMP.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

use App\Models\Sensor;
use App\Models\StateTranslation;
use LibreNMS\Enum\Severity;
use LibreNMS\OS\Pmp;

if ((int) SnmpQuery::get('WHISP-BOX-MIBV2-MIB::cnMaestroEnable.0')->value() !== 1) {
    return;
}

$status = SnmpQuery::get('WHISP-BOX-MIBV2-MIB::cnMaestroStatus.0')->value();

if ($status === '') {
    return;
}

$stateName = 'cnMaestroConnectionStatus';
app('sensor-discovery')->discover(new Sensor([
    'poller_type' => 'snmp',
    'sensor_class' => 'state',
    'sensor_oid' => '.1.3.6.1.4.1.161.19.3.3.1.260.0',
    'sensor_index' => $stateName,
    'sensor_type' => $stateName,
    'sensor_descr' => 'cnMaestro Connection Status',
    'sensor_current' => Pmp::cnMaestroConnectionStatus($status),
]))->withStateTranslations($stateName, [
    StateTranslation::define('Connected', 0, Severity::Ok),
    StateTranslation::define('Disconnected', 1, Severity::Error),
    StateTranslation::define('Connecting', 2, Severity::Warning),
    StateTranslation::define('Device Approval Pending', 3, Severity::Warning),
    StateTranslation::define('Unknown', 4, Severity::Unknown),
]);
