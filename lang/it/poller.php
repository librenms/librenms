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
                'help' => 'Amount of poller workers to spawn on this node.',
            ],
            'poller_frequency' => [
                'description' => 'Poller Frequency (Warning!)',
                'help' => 'How often to poll devices on this node.  Warning! Changing this without fixing rrd files will break graphs. See docs for more info.',
            ],
            'poller_down_retry' => [
                'description' => 'Device Down Retry',
                'help' => 'If a device is down when polling is attempted on this node. This is the amount of time to wait before retrying.',
            ],
            'discovery_enabled' => [
                'description' => 'Discovery Enabled',
                'help' => 'Enable discovery workers on this node.',
            ],
            'discovery_workers' => [
                'description' => 'Discovery Workers',
                'help' => 'Amount of discovery workers to run on this node.  Setting too high can cause overload.',
            ],
            'discovery_frequency' => [
                'description' => 'Discovery Frequency',
                'help' => 'How often to run device discovery on this node.  Default is 4 times a day.',
            ],
            'services_enabled' => [
                'description' => 'Services Enabled',
                'help' => 'Enable services workers on this node.',
            ],
            'services_workers' => [
                'description' => 'Services Workers',
                'help' => 'Amount of services workers on this node.',
            ],
            'services_frequency' => [
                'description' => 'Services Frequency',
                'help' => 'How often to run services on this node.  This should match poller frequency.',
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
                'help' => 'How often alert rules are checked on this node.  Note that data is only updated based on poller frequency.',
            ],
            'ping_enabled' => [
                'description' => 'Fast Ping Enabled',
                'help' => 'Fast Ping just pings devices to check if they are up or down',
            ],
            'ping_frequency' => [
                'description' => 'Ping Frequency',
                'help' => 'How often to check ping on this node.  Warning! If you change this you must make additional changes.  Check the Fast Ping docs.',
            ],
            'update_enabled' => [
                'description' => 'Daily Maintenance Enabled',
                'help' => 'Run daily.sh maintenance script and restart the dispatcher service afterwards.',
            ],
            'update_frequency' => [
                'description' => 'Maintenance Frequency',
                'help' => 'How often to run daily maintenance on this node. Default is 1 Day. It is highly suggested not to change this.',
            ],
            'loglevel' => [
                'description' => 'Log Level',
                'help' => 'Log level of the dispatch service.',
            ],
            'watchdog_enabled' => [
                'description' => 'Watchdog Enabled',
                'help' => 'Watchdog monitors the log file and restarts the service it it has not been updated',
            ],
            'watchdog_log' => [
                'description' => 'Log File to Watch',
                'help' => 'Default is the LibreNMS log file.',
            ],
        ],
        'units' => [
            'seconds' => 'Seconds',
            'workers' => 'Workers',
        ],
    ],
];
