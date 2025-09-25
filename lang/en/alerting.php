<?php

return [
    'maintenance' => [
        'maintenance' => 'Maintenance',
        'behavior' => [
            'options' => [
                'skip_alerts' => 'Skip alerts',
                'mute_alerts' => 'Mute alerts',
                'run_alerts' => 'Run alerts',
            ],
            'tooltip' => "- Skip alerts: Now new alerts will be created, and existing alerts won't be resolved.
        - Mute alerts: Alerts will created and resolved as usual, but any kind of user notification (like e-mail) is suppressed
        - Run alerts: Alerts are run as usual, users are notified. This option leads essentially to a 'cosmetic only' maintenance",
        ],
    ],
    'alert_log' => [
        'device_group_required' => 'Please select a device or device group to view all alerts.',
        'severities' => [
            1 => 'Ok, warning and critical',
            2 => 'Warning and critical',
            3 => 'Critical',
            4 => 'OK',
            5 => 'Warning',
            6 => 'Critical',
        ],
    ],
];
