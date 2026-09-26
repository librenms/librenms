<?php

namespace LibreNMS\Polling\Secrets\Data;

readonly class SnmpSecretData implements SecretData
{
    public function __construct(
        public string $version = 'v2c',
        public ?string $community = null,
        public ?string $authlevel = 'noAuthNoPriv',
        public ?string $authname = null,
        public ?string $authpass = null,
        public ?string $authalgo = 'SHA',
        public ?string $cryptoalgo = 'AES',
        public ?string $cryptopass = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            version: (string) ($data['version'] ?? 'v2c'),
            community: isset($data['community']) ? (string) $data['community'] : null,
            authlevel: isset($data['authlevel']) ? (string) $data['authlevel'] : 'noAuthNoPriv',
            authname: isset($data['authname']) ? (string) $data['authname'] : null,
            authpass: isset($data['authpass']) ? (string) $data['authpass'] : null,
            authalgo: isset($data['authalgo']) ? (string) $data['authalgo'] : 'SHA',
            cryptoalgo: isset($data['cryptoalgo']) ? (string) $data['cryptoalgo'] : 'AES',
            cryptopass: isset($data['cryptopass']) ? (string) $data['cryptopass'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
