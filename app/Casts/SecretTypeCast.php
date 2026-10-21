<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Enum\SecretType;

/**
 * Casts the secret type, unknown types (such as ones added by another branch) are null instead of throwing.
 *
 * @implements CastsAttributes<SecretType|null, SecretType|string|null>
 */
class SecretTypeCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?SecretType
    {
        return is_string($value) ? SecretType::tryFrom($value) : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof SecretType) {
            return $value->value;
        }

        return is_string($value) ? $value : null;
    }
}
