<?php

use LibreNMS\RRD\RrdDefinition;

// CISCO-VPDN-MGMT-MIB::cvpdnTunnelTotal.0 = Gauge32: 0 tunnels
// CISCO-VPDN-MGMT-MIB::cvpdnSessionTotal.0 = Gauge32: 0 users
// CISCO-VPDN-MGMT-MIB::cvpdnDeniedUsersTotal.0 = Counter32: 0 attempts
// CISCO-VPDN-MGMT-MIB::cvpdnSystemTunnelTotal.l2tp = Gauge32: 437 tunnels
// CISCO-VPDN-MGMT-MIB::cvpdnSystemSessionTotal.l2tp = Gauge32: 1029 sessions
// CISCO-VPDN-MGMT-MIB::cvpdnSystemDeniedUsersTotal.l2tp = Counter32: 0 attempts
// CISCO-VPDN-MGMT-MIB::cvpdnSystemClearSessions.0 = INTEGER: none(1)
if ($device['os_group'] == 'cisco') {
    $data = snmpwalk_cache_oid($device, 'cvpdnSystemEntry', null, 'CISCO-VPDN-MGMT-MIB');

    foreach ((array) $data as $type => $vpdn) {
        if ($vpdn['cvpdnSystemTunnelTotal'] || $vpdn['cvpdnSystemSessionTotal']) {
            $rrd_name = ['vpdn', $type];
            $rrd_def = RrdDefinition::make()
                ->addDataset('tunnels', 'GAUGE', 0)
                ->addDataset('sessions', 'GAUGE', 0)
                ->addDataset('denied', 'COUNTER', 0, 100000);

            $fields = [
                'tunnels'   => $vpdn['cvpdnSystemTunnelTotal'],
                'sessions'  => $vpdn['cvpdnSystemSessionTotal'],
                'denied'    => $vpdn['cvpdnSystemDeniedUsersTotal'],
            ];

            $tags = compact('type', 'rrd_name', 'rrd_def');
            data_update($device, 'vpdn', $tags, $fields);

            $os->enableGraph("vpdn_sessions_$type");
            $os->enableGraph("vpdn_tunnels_$type");

            echo " Cisco VPDN ($type) ";
        }
    }

    unset($data, $vpdn, $type, $rrd_filename);
}//end if
