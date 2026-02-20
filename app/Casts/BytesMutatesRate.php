<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Util\Number;

class BytesMutatesRate implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $bpsKey = match ($key) {
            'bytes_in' => 'bps_in',
            'bytes_out' => 'bps_out',
            default => null,
        };

        if ($bpsKey === null) {
            return [$key => $value];
        }

        if (empty($model->last_polled)) {
            return [
                $key => $value,
                $bpsKey => 0,
            ];
        }

        return [
            $key => $value,
            $bpsKey => Number::calculateRate(
                (string) ($model->getAttribute('bytes_in') ?: '0'),
                (string) ($value ?: '0'),
                $model->last_polled,
                time()
            ) * 8,
        ];
    }
}
