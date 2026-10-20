<?php

namespace LibreNMS\Polling\Secrets\Data;

readonly class ProxmoxSecretData implements SecretData
{
    public function __construct(
        public string $tokenId = '',
        public string $tokenSecret = '',
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            tokenId: (string) ($data['token_id'] ?? ''),
            tokenSecret: (string) ($data['token_secret'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token_id' => $this->tokenId,
            'token_secret' => $this->tokenSecret,
        ];
    }
}
