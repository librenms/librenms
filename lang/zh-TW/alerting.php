<?php

return [
    'maintenance' => [
        'maintenance' => '維護',
        'behavior' => [
            'options' => [
                'skip_alerts' => '略過警報',
                'mute_alerts' => '靜音警報',
                'run_alerts' => '執行警報',
            ],
            'tooltip' => '- 略過警報：不會建立新的警報，且現有警報也不會被解除。
        - 靜音警報：警報會如常建立與解除，但會抑制任何形式的使用者通知（例如電子郵件）。
        - 執行警報：警報如常執行，並通知使用者。此選項基本上等同於「僅外觀上的」維護模式。',
        ],
    ],
];
