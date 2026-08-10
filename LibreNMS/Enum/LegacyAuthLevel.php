<?php

namespace LibreNMS\Enum;

enum LegacyAuthLevel: int
{
    case User = 1;
    case GlobalRead = 5;
    case Admin = 10;
    case Demo = 11;

    public function fromName(string $name): ?LegacyAuthLevel
    {
        return match ($name) {
            'admin' => LegacyAuthLevel::Admin,
            'user' => LegacyAuthLevel::User,
            'global-read', 'global_read' => LegacyAuthLevel::GlobalRead,
            'demo' => LegacyAuthLevel::Demo,
            default => null
        };
    }

    public function getName(): string
    {
        return match ($this) {
            self::GlobalRead => 'global-read',
            self::Admin => 'admin',
            self::User => 'user',
            self::Demo => 'demo',
        };
    }
}
