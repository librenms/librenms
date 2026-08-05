<?php

return [
    'v1' => [
        // Opt-in switch for the whole v1 API. When disabled, no v1 routes
        // are registered and /api/v1/* returns 404.
        'enabled' => (bool) env('API_V1_ENABLED', false),
    ],
];
