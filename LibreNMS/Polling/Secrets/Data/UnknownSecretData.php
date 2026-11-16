<?php

namespace LibreNMS\Polling\Secrets\Data;

readonly class UnknownSecretData implements SecretData
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $data = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
