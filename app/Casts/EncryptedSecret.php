<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Exceptions\SecretDecryptionException;
use WeakMap;

/**
 * Encrypts and decrypts secret credential data stored as JSON arrays.
 *
 * Intended for the Secret model's `data` column, which is not nullable.
 * Empty data is stored as an encrypted empty array.
 *
 * Eloquent only caches objects returned by casts, so the decrypted array is cached here for each model instance.
 * The cache follows the stored payload, a changed value is decrypted again, and is freed with the model.
 *
 * @implements CastsAttributes<array<string, mixed>, array<string, mixed>|null>
 */
class EncryptedSecret implements CastsAttributes
{
    /**
     * Eloquent creates a new caster for every access, so the cache is shared
     *
     * @var WeakMap<Model, array<string, array{string, array<string, mixed>}>>|null keyed by model, then attribute
     */
    private static ?WeakMap $decrypted = null;

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (blank($value)) {
            return [];
        }

        self::$decrypted ??= new WeakMap;
        [$cachedValue, $cachedData] = self::$decrypted[$model][$key] ?? [null, []];
        if ($cachedValue === $value) {
            return $cachedData;
        }

        try {
            $decrypted = decrypt($value);
            /** @var array<string, mixed>|scalar|null $decoded */
            $decoded = json_decode((string) $decrypted, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($decoded)) {
                throw SecretDecryptionException::failedToDecrypt('Decrypted payload is not a valid JSON array.');
            }

            $cache = self::$decrypted[$model] ?? [];
            $cache[$key] = [(string) $value, $decoded];
            self::$decrypted[$model] = $cache;

            return $decoded;
        } catch (DecryptException $e) {
            throw SecretDecryptionException::failedToDecrypt($e->getMessage());
        } catch (\JsonException $e) {
            throw SecretDecryptionException::failedToDecrypt('Failed to decode JSON: ' . $e->getMessage());
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (! is_array($value)) {
            $value = [];
        }

        try {
            return encrypt(json_encode($value, JSON_THROW_ON_ERROR));
        } catch (EncryptException $e) {
            throw SecretDecryptionException::failedToEncrypt($e->getMessage());
        } catch (\JsonException $e) {
            throw SecretDecryptionException::failedToEncrypt('Failed to encode JSON: ' . $e->getMessage());
        }
    }
}
