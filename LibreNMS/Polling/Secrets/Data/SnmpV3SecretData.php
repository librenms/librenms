<?php

namespace LibreNMS\Polling\Secrets\Data;

use LibreNMS\Polling\Secrets\Definitions\SnmpV3SecretDefinition;

readonly class SnmpV3SecretData implements SecretData
{
    public function __construct(
        public ?string $authname = null,
        public string $authlevel = SnmpV3SecretDefinition::DEFAULT_AUTHLEVEL,
        public ?string $authpass = null,
        public string $authalgo = SnmpV3SecretDefinition::DEFAULT_AUTHALGO,
        public string $cryptoalgo = SnmpV3SecretDefinition::DEFAULT_CRYPTOALGO,
        public ?string $cryptopass = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            authname: isset($data['authname']) ? (string) $data['authname'] : null,
            authlevel: (string) ($data['authlevel'] ?? SnmpV3SecretDefinition::DEFAULT_AUTHLEVEL),
            authpass: isset($data['authpass']) ? (string) $data['authpass'] : null,
            authalgo: (string) ($data['authalgo'] ?? SnmpV3SecretDefinition::DEFAULT_AUTHALGO),
            cryptoalgo: (string) ($data['cryptoalgo'] ?? SnmpV3SecretDefinition::DEFAULT_CRYPTOALGO),
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
