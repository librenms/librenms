<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;

/**
 * Unrecognized stored values cast to Unknown instead of throwing, the stored value is kept.
 *
 * @template TEnum of PollingMethodType|SecretType
 *
 * @implements CastsAttributes<TEnum|null, TEnum|string|null>
 */
abstract class StoredTypeEnum implements CastsAttributes, SerializesCastableAttributes
{
    /** Unknown can't tell different stored values apart */
    public bool $withoutObjectCaching = true;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === PollingMethodType::Unknown || $value === SecretType::Unknown) {
            return isset($attributes[$key]) ? (string) $attributes[$key] : $value->value; // keep the stored type
        }

        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return $value === null ? null : (string) $value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return isset($attributes[$key]) ? (string) $attributes[$key] : null;
    }
}
