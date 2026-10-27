<?php

namespace App\Casts;

use Illuminate\Database\Eloquent\Model;
use LibreNMS\Enum\PollingMethodType;

/**
 * @extends StoredTypeEnum<PollingMethodType>
 */
class PollingMethodTypeCast extends StoredTypeEnum
{
    protected function enumClass(): string
    {
        return PollingMethodType::class;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?PollingMethodType
    {
        return $value === null ? null : PollingMethodType::fromStored((string) $value);
    }
}
