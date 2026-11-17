<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CompressedJson implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return self::decode($value);
    }

    /**
     * Decode a compressed json value, returning an empty array if it is missing or invalid.
     *
     * @return array<array-key, mixed>
     */
    public static function decode(mixed $value): array
    {
        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode((string) @gzuncompress($value), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return gzcompress(json_encode($value));
    }
}
