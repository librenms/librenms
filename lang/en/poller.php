<?php

return [
    'settings' => [
        'settings' => [
            'poller_groups' => [
                'description' => 'Assigned Groups',
                'help' => 'This node will only take action on devices in these poller groups.',
            ],
            'poller_enabled' => [
                'description' => 'Poller Enabled',
                'help' => 'Enable poller workers on this node.',
            ],
            'poller_workers' => [
                'description' => 'Poller Workers',
                'help' => 'Number of poller workers to spawn on this node.',
            ],
            'poller_frequency' => [
                'description' => 'Poller Frequency (Warning!)',
                'help' => 'How often to poll devices on this node. Warning! If you change this without a fix to the rrd files, graphs break. For more information, see the documentation.',
            ],
            'poller_down_retry' => [
                'description' => 'Device Down Retry',
                'help' => 'Time to wait before a retry when a device is down at the poll attempt on this node.',
            ],
            'discovery_enabled' => [
                'description' => 'Discovery Enabled',
                'help' => 'Enable discovery workers on this node.',
            ],
            'discovery_workers' => [
                'description' => 'Discovery Workers',
                'help' => 'Number of discovery workers to run on this node. A value that is too high can cause an overload.',
            ],
            'discovery_frequency' => [
                'description' => 'Discovery Frequency',
                'help' => 'How often to run device discovery on this node. The default is 4 times a day.',
            ],
            'services_enabled' => [
                'description' => 'Services Enabled',
                'help' => 'Enable services workers on this node.',
            ],
            'services_workers' => [
                'description' => 'Services Workers',
                'help' => 'Number of services workers on this node.',
            ],
            'services_frequency' => [
                'description' => 'Services Frequency',
                'help' => 'How often to run services on this node. This must match the poller frequency.',
            ],
            'billing_enabled' => [
                'description' => 'Billing Enabled',
                'help' => 'Enable billing workers on this node.',
            ],
            'billing_frequency' => [
                'description' => 'Billing Frequency',
                'help' => 'How often to collect billing data on this node.',
            ],
            'billing_calculate_frequency' => [
                'description' => 'Billing Calculate Frequency',
                'help' => 'How often to calculate bill usage on this node.',
            ],
            'alerting_enabled' => [
                'description' => 'Alerting Enabled',
                'help' => 'Enable the alerting worker on this node.',
            ],
            'alerting_frequency' => [
                'description' => 'Alerting Frequency',
                'help' => 'How often to check alert rules on this node. Data is updated only at the poller frequency.',
            ],
            'ping_enabled' => [
                'description' => 'Fast Ping Enabled',
                'help' => 'Fast Ping pings devices to check if they are up or down',
            ],
            'ping_frequency' => [
                'description' => 'Ping Frequency',
                'help' => 'How often to check ping on this node. Warning! If you change this, you must make additional changes. See the Fast Ping documentation.',
            ],
            'update_enabled' => [
                'description' => 'Daily Maintenance Enabled',
                'help' => 'Run daily.sh maintenance script and restart the dispatcher service afterwards.',
            ],
            'update_frequency' => [
                'description' => 'Maintenance Frequency',
                'help' => 'How often to run daily maintenance on this node. The default is 1 day. Do not change this.',
            ],
            'loglevel' => [
                'description' => 'Log Level',
                'help' => 'Log level of the dispatch service.',
            ],
            'watchdog_enabled' => [
                'description' => 'Watchdog Enabled',
                'help' => 'The watchdog monitors the log file and restarts the service if the log file does not update',
            ],
            'watchdog_log' => [
                'description' => 'Log File to Watch',
                'help' => 'The default is the LibreNMS log file.',
            ],
        ],
        'units' => [
            'seconds' => 'Seconds',
            'workers' => 'Workers',
        ],
    ],
];
