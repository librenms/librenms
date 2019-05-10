<?php

use LibreNMS\Config;

if (Config::get('discover_services')) {

    // Services
    if ($device['type'] == 'server') {
        $oids = trim(snmp_walk($device, '.1.3.6.1.2.1.6.13.1.1.0.0.0.0', '-Osqn'));
        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if ($data) {
                list($oid, $tcpstatus) = explode(' ', $data);
                if (trim($tcpstatus) == 'listen') {
                    $split_oid = explode('.', $oid);
                    $tcp_port  = $split_oid[(count($split_oid) - 6)];
                    if ($service = getservbyport($tcp_port, 'tcp')) {
                        discover_service($device, $service);
                    }
                }
            }
        }
    }

    echo "\n";
}
