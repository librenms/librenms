<?php

namespace LibreNMS\Enum;

enum LegacyAuthLevel: int
{
    case  user = 1;
    case global_read = 5;
    case admin = 10;
    case demo = 11;

    public function fromName(string $name): ?LegacyAuthLevel
    {
        return match ($name) {
            'admin' => LegacyAuthLevel::admin,
            'user' => LegacyAuthLevel::user,
            'global-read', 'global_read' => LegacyAuthLevel::global_read,
            'demo' => LegacyAuthLevel::demo,
            default => null
        };
    }

    public function getName(): string
    {
        if ($this == LegacyAuthLevel::global_read) {
            return 'global-read';
        }

        return $this->name;
    }
}
