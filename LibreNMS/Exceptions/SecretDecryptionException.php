<?php

namespace LibreNMS\Exceptions;

use Exception;

class SecretDecryptionException extends Exception
{
    public static function failedToDecrypt(?string $reason = null): self
    {
        return new self($reason ?? 'Failed to decrypt secret data.');
    }
}
