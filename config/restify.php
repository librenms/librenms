<?php

use Binaryk\LaravelRestify\Http\Middleware\AuthorizeRestify;
use Binaryk\LaravelRestify\Http\Middleware\DispatchRestifyStartingEvent;

return [
    "auth" => [
        "table" => "users",
        "provider" => "sanctum",
        "user_model" => "\\App\\Models\\User",
        "token_ttl" => env("RESTIFY_TOKEN_TTL", null),
    ],

    "base" => "/api/v1",

    "middleware" => [
        \App\Http\Middleware\EnforceJsonApi::class,
        "auth:sanctum",
        DispatchRestifyStartingEvent::class,
        AuthorizeRestify::class,
    ],

    "logs" => [
        "repository" => null,
        "enable" => false,
        "all" => false,
    ],

    "search" => [
        "case_sensitive" => false,
        "use_joins_for_belongs_to" => false,
    ],

    "repositories" => [
        "serialize_index_meta" => false,
        "serialize_show_meta" => false,
        "cache" => [
            "enabled" => false,
            "ttl" => 300,
            "store" => null,
            "skip_authenticated" => false,
            "enable_in_tests" => false,
            "tags" => ["restify", "repositories"],
        ],
    ],

    "cache" => [
        "policies" => [
            "enabled" => false,
            "ttl" => 300,
        ],
    ],
];
