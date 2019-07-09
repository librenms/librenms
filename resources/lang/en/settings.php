<?php

return [
    'groups' => [
        'alerting' => 'Alerting',
        'auth' => 'Authentication',
        'external' => 'External',
        'global' => 'Global',
        'os' => 'OS',
        'poller' => 'Poller',
        'system' => 'System',
        'webui' => 'Web UI',
    ],
    'sections' => [
        'alerting' => [
            'general' => 'General Alert Settings',
            'email' => 'Email Options',
        ],
        'auth' => [
            'general' => 'General Authentication Settings',
            'ad' => 'Active Directory Settings',
            'ldap' => 'LDAP Settings'
        ],
        'external' => [
            'location' => 'Location Settings',
            'oxidized' => 'Oxidized Integration',
            'paths' => 'Binary Locations',
            'peeringdb' => 'PeeringDB Integration',
            'rrdtool' => 'RRDTool Setup',
            'unix-agent' => 'Unix-Agent Integration',
        ],
        'poller' => [
            'ping' => 'Ping',
        ],
        'system' => [
            'cleanup' => 'Cleanup',
            'updates' => 'Updates',
        ],
        'webui' => [
            'availability-map' => 'Availability Map Settings',
            'graph' => 'Graph Settings',
            'dashboard' => 'Dashboard Settings',
            'search' => 'Search Settings',
        ]
    ],
    'validate' => [
        'boolean' => ':value is not a valid boolean',
        'email' => ':value is not a valid email',
        'integer' => ':value is not an integer',
        'password' => 'The password is incorrect',
        'select' => ':value is not an allowed value',
        'text' => ':value is not allowed',
    ]
];
