<?php

return [
    'tabs' => [
        'alerting' => 'Alerting',
        'external' => 'External',
        'global' => 'Global',
        'os' => 'OS',
        'webui' => 'Web UI',
    ],
    'sections' => [
        'alerting' => [
            'general' => 'General Alert Settings',
            'email' => 'Email Options'
        ],
        'external' => [
            'location' => 'Location Geocoding',
            'oxidized' => 'Oxidized Integration',
            'paths' => 'Binary Locations',
            'peeringdb' => 'PeeringDB Integration',
            'rrdtool' => 'RRDTool Setup',
            'unix-agent' => 'Unix-Agent Integration',
        ],
        'webui' => [
            'availability-map' => 'Availability Map Settings',
            'graph' => 'Graph Settings',
            'dashboard' => 'Dashboard Settings',
            'search' => 'Search Settings',
        ]
    ],
];
