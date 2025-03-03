<?php

return [
    'settings' => [
        'settings' => [
            'poller_groups' => [
                'description' => '指派群組',
                'help' => 'This node will only take action on devices in these poller groups.',
            ],
            'poller_enabled' => [
                'description' => '啟用輪詢器',
                'help' => 'Enable poller workers on this node.',
            ],
            'poller_workers' => [
                'description' => '輪詢器工作',
                'help' => 'Amount of poller workers to spawn on this node.',
            ],
            'poller_frequency' => [
                'description' => '輪詢器頻率 (警告!)',
                'help' => 'How often to poll devices on this node.  Warning! Changing this without fixing rrd files will break graphs. See docs for more info.',
            ],
            'poller_down_retry' => [
                'description' => '裝置斷線重試',
                'help' => 'If a device is down when polling is attempted on this node. This is the amount of time to wait before retrying.',
            ],
            'discovery_enabled' => [
                'description' => '啟用探索',
                'help' => 'Enable discovery workers on this node.',
            ],
            'discovery_workers' => [
                'description' => '探索工作',
                'help' => 'Amount of discovery workers to run on this node.  Setting too high can cause overload.',
            ],
            'discovery_frequency' => [
                'description' => '探索頻率',
                'help' => 'How often to run device discovery on this node.  Default is 4 times a day.',
            ],
            'services_enabled' => [
                'description' => '啟用服務',
                'help' => 'Enable services workers on this node.',
            ],
            'services_workers' => [
                'description' => '服務工作',
                'help' => 'Amount of services workers on this node.',
            ],
            'services_frequency' => [
                'description' => '服務頻率',
                'help' => 'How often to run services on this node.  This should match poller frequency.',
            ],
            'billing_enabled' => [
                'description' => '啟用計費',
                'help' => 'Enable billing workers on this node.',
            ],
            'billing_frequency' => [
                'description' => '計費頻率',
                'help' => 'How often to collect billing data on this node.',
            ],
            'billing_calculate_frequency' => [
                'description' => '計費頻率',
                'help' => 'How often to calculate bill usage on this node.',
            ],
            'alerting_enabled' => [
                'description' => '啟用警報',
                'help' => 'Enable the alerting worker on this node.',
            ],
            'alerting_frequency' => [
                'description' => '警報頻率',
                'help' => 'How often alert rules are checked on this node.  Note that data is only updated based on poller frequency.',
            ],
            'ping_enabled' => [
                'description' => '啟用 Fast Ping',
                'help' => 'Fast Ping just pings devices to check if they are up or down',
            ],
            'ping_frequency' => [
                'description' => 'Ping 頻率',
                'help' => 'How often to check ping on this node.  Warning! If you change this you must make additional changes.  Check the Fast Ping docs.',
            ],
            'update_enabled' => [
                'description' => '啟用每日維護',
                'help' => 'Run daily.sh maintenance script and restart the dispatcher service afterwards.',
            ],
            'update_frequency' => [
                'description' => '維護頻率',
                'help' => 'How often to run daily maintenance on this node. Default is 1 Day. It is highly suggested not to change this.',
            ],
            'loglevel' => [
                'description' => '記錄層級',
                'help' => 'Log level of the dispatch service.',
            ],
            'watchdog_enabled' => [
                'description' => '啟用看門狗',
                'help' => 'Watchdog monitors the log file and restarts the service it it has not been updated',
            ],
            'watchdog_log' => [
                'description' => '看門狗記錄檔',
                'help' => 'Default is the LibreNMS log file.',
            ],
        ],
        'units' => [
            'seconds' => '秒',
            'workers' => '工作數',
        ],
    ],
];
