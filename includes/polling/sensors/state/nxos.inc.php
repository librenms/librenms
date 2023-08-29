<?php

if ($device['os_group'] == 'nxos') {
    if ($sensor['sensor_type'] === 'cErrDisableIfStatusCause') {
        $states = [
            ['value' => 0, 'graph' => 1, 'generic' => 0, 'descr' => 'OK'],
            ['value' => 1, 'graph' => 1, 'generic' => 2, 'descr' => 'udld'],
            ['value' => 2, 'graph' => 1, 'generic' => 2, 'descr' => 'bpduGuard'],
            ['value' => 3, 'graph' => 1, 'generic' => 2, 'descr' => 'channelMisconfig'],
            ['value' => 4, 'graph' => 1, 'generic' => 2, 'descr' => 'pagpFlap'],
            ['value' => 5, 'graph' => 1, 'generic' => 2, 'descr' => 'dtpFlap'],
            ['value' => 6, 'graph' => 1, 'generic' => 2, 'descr' => 'linkFlap'],
            ['value' => 7, 'graph' => 1, 'generic' => 2, 'descr' => 'l2ptGuard'],
            ['value' => 8, 'graph' => 1, 'generic' => 2, 'descr' => 'dot1xSecurityViolation'],
            ['value' => 9, 'graph' => 1, 'generic' => 2, 'descr' => 'portSecurityViolation'],
            ['value' => 10, 'graph' => 1, 'generic' => 2, 'descr' => 'gbicInvalid'],
            ['value' => 11, 'graph' => 1, 'generic' => 2, 'descr' => 'dhcpRateLimit'],
            ['value' => 12, 'graph' => 1, 'generic' => 2, 'descr' => 'unicastFlood'],
            ['value' => 13, 'graph' => 1, 'generic' => 2, 'descr' => 'vmps'],
            ['value' => 14, 'graph' => 1, 'generic' => 2, 'descr' => 'stormControl'],
            ['value' => 15, 'graph' => 1, 'generic' => 2, 'descr' => 'inlinePower'],
            ['value' => 16, 'graph' => 1, 'generic' => 2, 'descr' => 'arpInspection'],
            ['value' => 17, 'graph' => 1, 'generic' => 2, 'descr' => 'portLoopback'],
        ];
        $sensor_value = $states[$sensor_value];
    }
}
