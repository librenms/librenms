<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
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
     * @return class-string<TEnum>
     */
    abstract protected function enumClass(): string;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof \BackedEnum) {
            if (! $value instanceof ($this->enumClass())) {
                throw new InvalidArgumentException("$key must be a {$this->enumClass()}, got " . $value::class);
            }

            if ($value->isUnknown()) {
                return isset($attributes[$key]) ? (string) $attributes[$key] : $value->value; // keep the stored type
            }

            return $value->value;
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
