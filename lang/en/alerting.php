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
            'tooltip' => '- Skip alerts: No new alerts are created, and existing alerts are not resolved.
        - Mute alerts: Alerts are created and resolved as usual, but all user notifications such as email are suppressed.
        - Run alerts: Alerts run as usual and users are notified. This option makes the maintenance cosmetic only.',
        ],
        'title' => 'Title',
    ],
];
