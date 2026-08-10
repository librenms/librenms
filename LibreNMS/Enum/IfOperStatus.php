<?php

namespace LibreNMS\Enum;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

enum IfOperStatus: string implements Castable
{
    case Up = 'up';
    case Down = 'down';
    case Testing = 'testing';
    case Unknown = 'unknown';
    case Dormant = 'dormant';
    case NotPresent = 'notPresent';
    case LowerLayerDown = 'lowerLayerDown';

    public static function tryFromNullable(mixed $value): ?self
    {
        return match (true) {
            $value instanceof self => $value,
            $value === null, $value === '' => null,
            default => self::tryFrom((string) $value),
        };
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get(Model $model, string $key, mixed $value, array $attributes): ?IfOperStatus
            {
                return IfOperStatus::tryFromNullable($value);
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): ?string
            {
                return IfOperStatus::tryFromNullable($value)?->value;
            }
        };
    }
}
