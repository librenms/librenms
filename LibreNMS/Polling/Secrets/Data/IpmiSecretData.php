<?php

namespace LibreNMS\Polling\Secrets\Data;

readonly class IpmiSecretData
{
    public function __construct(
        public string $username = '',
        public string $password = '',
        public string $kgKey = '',
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            username: (string) ($data['username'] ?? ''),
            password: (string) ($data['password'] ?? ''),
            kgKey: (string) ($data['kg_key'] ?? $data['kgKey'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
            'kg_key' => $this->kgKey,
        ];
    }
}
