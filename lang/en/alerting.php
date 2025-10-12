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
    'rules' => [
        'setup' => [
            'legend' => 'Rule setup',
            'name' => [
                'label' => 'Rule name',
                'help' => 'A display name for this alert rule',
            ],
            'import' => [
                'button' => 'Import from',
                'sql_query' => 'SQL Query',
                'old_format' => 'Old Format',
                'collection' => 'Collection',
                'alert_rule' => 'Alert Rule',
            ],
            'builder' => [
                'help' => 'Build an SQL query to match rows from the database. If the query returns row(s) this alert will trigger.',
            ],
            'override_sql' => [
                'label' => 'Override SQL',
                'help' => 'Override the SQL query above with a custom SQL query.',
            ],
            'sql' => [
                'label' => 'SQL',
                'help' => 'Optional: Provide a raw SQL WHERE clause to override the builder. Must include one ? for device_id.',
            ],
            'invert_match' => [
                'label' => 'Invert match result',
                'help' => 'Invert the match. If the rule matches, the alert is considered OK.',
            ],
        ],
        'targeting' => [
            'legend' => 'Targeting',
            'maps' => [
                'label' => 'Devices, groups, and locations',
                'help' => 'Restrict this alert rule to the selected devices, groups, or locations.',
            ],
            'invert_map' => [
                'label' => 'Run on all devices except selected',
                'help' => 'If ON, alert rule checks will run on all devices except the selected devices and groups.',
            ],
        ],
        'notifications' => [
            'legend' => 'Notifications',
            'severity' => [
                'label' => 'Severity',
                'options' => [
                    'ok' => 'OK',
                    'warning' => 'Warning',
                    'critical' => 'Critical',
                ],
                'help' => 'How to display the alert.  OK: green, Warning: yellow, Critical: red',
            ],
            'delay' => [
                'label' => 'Delay',
                'help' => 'How long to wait before issuing a notification. If the alert clears before the delay, no notification will be issued. Note that generally, data is only updated when the poller runs. (s,m,h,d)',
            ],
            'count' => [
                'label' => 'Max alerts',
                'help' => 'How many notifications to issue while active before stopping. -1 means no limit. If interval is 0, this has no effect.',
            ],
            'interval' => [
                'label' => 'Interval',
                'help' => 'How often to re-issue notifications while this alert is active. 0 means notify once. This is affected by the poller interval. (s,m,h,d)',
            ],
            'mute' => [
                'label' => 'Mute alerts',
                'help' => 'Show alert status in the webui, but do not issue notifications.',
            ],
            'recovery' => [
                'label' => 'Recovery alerts',
                'help' => 'Send recovery notification when alert clears.',
            ],
            'acknowledgement' => [
                'label' => 'Acknowledgement alerts',
                'help' => 'Send acknowledgement notification when alert is acknowledged.',
            ],
            'delivery' => [
                'legend' => 'Delivery transports',
                'label' => 'Transports',
                'help' => 'Restricts this alert rule to specified transports.',
            ],
        ],
        'templates' => [
            'legend' => 'Templates',
            'label' => 'Template',
            'use_default' => 'Use default template',
            'help' => 'Choose template for all transports. You can override per transport below.',
            'per_transport' => [
                'label' => 'Per-transport overrides',
                'help' => 'After selecting transports above, choose a template for any you want to override.',
                'no_override' => '— No Override —',
            ],
        ],
        'notes' => [
            'legend' => 'Notes & Documentation',
            'proc_url' => [
                'label' => 'Procedure URL',
                'help' => 'A link to some documentation on how to handle this alert. This can be included in notifications.',
            ],
            'notes' => [
                'label' => 'Notes',
                'help' => 'A brief description for this alert rule',
            ],
        ],
        'placeholders' => [
            'maps' => 'Devices, Groups or Locations',
            'transports' => 'Use default transport',
        ],
        'messages' => [
            'failed_load_template' => 'Failed to load template',
            'failed_process_template' => 'Failed to process template',
            'failed_load_rule' => 'Failed to load rule',
            'failed_process_rule' => 'Failed to process rule',
            'invalid_rule' => 'Invalid rule, please complete the required fields',
            'prompt_sql_query' => 'Enter your SQL query:',
            'prompt_old_rule' => 'Enter your old alert rule:',
            'query_not_parsed' => 'Your query could not be parsed',
            'select' => 'Select',
        ],
    ],
];
