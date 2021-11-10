<?php

namespace LibreNMS\Polling\Secrets\Data;

interface SecretData
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
