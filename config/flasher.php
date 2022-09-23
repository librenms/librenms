<?php

return [
    'default' => 'template.librenms',

    'root_script' => null,

    'template_factory' => [
        'templates' => [
            'librenms' => [
                'view' => 'layouts.flasher-notification',
                'options' => [
                    'timeout' => 12000,
                    'style' => [
                        'top' => '55px',
                    ],
                ],
            ],
        ],
    ],
];
