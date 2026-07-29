<?php

namespace LibreNMS\Exceptions;

use RuntimeException;

class SecretDecryptionException extends RuntimeException
{
    public static function failedToDecrypt(?string $reason = null): self
    {
        return new self($reason ?? 'Failed to decrypt secret data.');
    }
}
