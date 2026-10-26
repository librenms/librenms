<?php

namespace App\Casts;

use Illuminate\Database\Eloquent\Model;
use LibreNMS\Enum\SecretType;

/**
 * @extends StoredTypeEnum<SecretType>
 */
class SecretTypeCast extends StoredTypeEnum
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?SecretType
    {
        return $value === null ? null : SecretType::fromStored((string) $value);
    }
}
