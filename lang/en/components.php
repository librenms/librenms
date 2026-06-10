<?php

return [
    'notification-subscription-status' => [
        'no-support' => 'This browser does not support notifications',
        'no-transport' => 'To enable browser notifications, there must be an alert transport referencing this user',
        'enabled' => 'Notifications enabled for this browser',
        'disabled' => 'Notifications disabled for this browser',
        'enable' => 'Enable',
        'disable' => 'Disable',
    ],

    'maintenance-mode' => [
        'button' => [
            'maintenance_mode' => 'Maintenance Mode',
            'device_under_maintenance' => 'Device under Maintenance',
        ],
        'titles' => [
            'device_maintenance' => 'Device Maintenance',
            'end_maintenance' => 'End Maintenance',
        ],
        'confirm' => [
            'end_prompt' => 'Are you sure you want to end maintenance for this device?',
        ],
        'form' => [
            'notes_label' => 'Notes:',
            'notes_placeholder' => 'Maintenance notes',
            'duration_label' => 'Duration:',
            'behavior_label' => 'Behavior:',
            'start_maintenance' => 'Start Maintenance',
            'end_maintenance' => 'End Maintenance',
        ],
        'errors' => [
            'enable' => 'An error occurred setting this device into maintenance mode',
            'disable' => 'An error occurred disabling maintenance mode',
        ],
    ],
];
