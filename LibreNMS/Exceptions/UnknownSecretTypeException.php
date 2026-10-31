<?php

namespace LibreNMS\Exceptions;

use App\Models\Secret;
use RuntimeException;

class UnknownSecretTypeException extends RuntimeException
{
    public static function forSecret(Secret $secret): self
    {
        return new self("Secret $secret->id has the type {$secret->typeName()}, which this version of LibreNMS does not know.");
    }
}
