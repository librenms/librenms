<?php

namespace LibreNMS\Polling\Secrets\Data;

use LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;

readonly class SnmpSecretData implements SecretData
{
    public function __construct(
        public string $version = SnmpSecretDefinition::DEFAULT_VERSION,
        public ?string $community = null,
        public ?string $authlevel = SnmpSecretDefinition::DEFAULT_AUTHLEVEL,
        public ?string $authname = null,
        public ?string $authpass = null,
        public ?string $authalgo = SnmpSecretDefinition::DEFAULT_AUTHALGO,
        public ?string $cryptoalgo = SnmpSecretDefinition::DEFAULT_CRYPTOALGO,
        public ?string $cryptopass = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            version: (string) ($data['version'] ?? SnmpSecretDefinition::DEFAULT_VERSION),
            community: isset($data['community']) ? (string) $data['community'] : null,
            authlevel: isset($data['authlevel']) ? (string) $data['authlevel'] : SnmpSecretDefinition::DEFAULT_AUTHLEVEL,
            authname: isset($data['authname']) ? (string) $data['authname'] : null,
            authpass: isset($data['authpass']) ? (string) $data['authpass'] : null,
            authalgo: isset($data['authalgo']) ? (string) $data['authalgo'] : SnmpSecretDefinition::DEFAULT_AUTHALGO,
            cryptoalgo: isset($data['cryptoalgo']) ? (string) $data['cryptoalgo'] : SnmpSecretDefinition::DEFAULT_CRYPTOALGO,
            cryptopass: isset($data['cryptopass']) ? (string) $data['cryptopass'] : null,
        );
    }

    /**
     * A secret with only a version has no credentials, the default credentials for that version are tried instead.
     */
    public function hasCredentials(): bool
    {
        return $this->version === 'v3' ? ! empty($this->authname) : ! empty($this->community);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
