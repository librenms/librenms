<?php

namespace LibreNMS\Exceptions;

use RuntimeException;

class SecretDecryptionException extends RuntimeException
{
    public static function failedToDecrypt(?string $reason = null): self
    {
        $message = 'Failed to decrypt secret data. Ensure APP_KEY in .env has not changed.';

        if ($reason) {
            $message .= " ($reason)";
        }

        return new self($message);
    }
}
