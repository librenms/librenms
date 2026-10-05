<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Exceptions\SecretDecryptionException;

/**
 * Encrypts and decrypts secret credential data stored as JSON arrays.
 *
 * Intended for the Secret model's `data` column, which is not nullable.
 * Empty data is stored as an encrypted empty array.
 *
 * @implements CastsAttributes<array<string, mixed>, array<string, mixed>|null>
 */
class EncryptedSecret implements CastsAttributes
{
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

        try {
            $decrypted = decrypt($value);
            $decoded = json_decode((string) $decrypted, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($decoded)) {
                throw SecretDecryptionException::failedToDecrypt('Decrypted payload is not a valid JSON array.');
            }

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
