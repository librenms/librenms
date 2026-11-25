<?php

namespace LibreNMS\Polling\Secrets\Data;

readonly class SnmpCommunitySecretData implements SecretData
{
    public function __construct(
        public string $community = '',
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            community: (string) ($data['community'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'community' => $this->community,
        ];
    }
}
