<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Exceptions\SecretDecryptionException;

/**
 * @implements CastsAttributes<array<string, mixed>, array<string, mixed>|string|null>
 */
class EncryptedArray implements CastsAttributes
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
            $decoded = json_decode((string) $decrypted, true);
            if (! is_array($decoded)) {
                throw SecretDecryptionException::failedToDecrypt('Decrypted payload is not a valid JSON array.');
            }

            return $decoded;
        } catch (DecryptException $e) {
            throw SecretDecryptionException::failedToDecrypt($e->getMessage());
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (! is_array($value) || empty($value)) {
            return null;
        }

        try {
            return encrypt(json_encode($value));
        } catch (EncryptException $e) {
            throw SecretDecryptionException::failedToDecrypt($e->getMessage());
        }
    }
}
