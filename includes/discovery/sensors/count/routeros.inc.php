<?php

use App\Models\Sensor;

/** @var \LibreNMS\OS\Routeros $os */

/*
 * Active PPPoE sessions, RouterOS answers CISCO-AAA-SESSION-MIB::casnActiveTableEntries.
 * Every RouterOS answers 0 without a PPP server, so the sensor is only created once there are sessions,
 * an existing sensor is kept at 0 so it can alert when all sessions drop.
 */
$pppoeSessions = SnmpQuery::mibDir('cisco')->get('CISCO-AAA-SESSION-MIB::casnActiveTableEntries.0')->value();

if (is_numeric($pppoeSessions) && ($pppoeSessions > 0 || $os->getDevice()->sensors()
    ->where('sensor_class', 'count')
    ->where('sensor_type', 'routeros')
    ->where('sensor_index', 'casnActiveTableEntries.0')
    ->exists())) {
    app('sensor-discovery')->discover(new Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'count',
        'sensor_oid' => '.1.3.6.1.4.1.9.9.150.1.1.1.0',
        'sensor_index' => 'casnActiveTableEntries.0',
        'sensor_type' => 'routeros',
        'sensor_descr' => 'PPPoE Session Count',
        'sensor_current' => $pppoeSessions,
    ]));
}
