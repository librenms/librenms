<?php

return [
    'default' => 'template.librenms',

    'use_cdn' => false,

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
