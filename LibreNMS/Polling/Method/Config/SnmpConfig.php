<?php

/**
 * SnmpConfig.php
 *
 * Value object holding SNMP connection settings and credentials for a target.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling\Method\Config;

use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Secrets\Data\SnmpCommunitySecretData;
use LibreNMS\Polling\Secrets\Data\SnmpV3SecretData;

final readonly class SnmpConfig extends PollingMethodConfig
{
    public function __construct(
        // Secrets
        public string $version,
        public ?string $community,
        public ?string $authname,
        public ?string $authpass,
        public ?string $authlevel,
        public ?string $authalgo,
        public ?string $cryptopass,
        public ?string $cryptoalgo,

        // Settings
        public string $transport,
        public int $port,
        public ?string $context,
        public int|float $timeout,
        public int $retries,
        public int $maxRepeaters,
        public int $maxOid,
        public bool $bulk,
        public string $portAssociationMode,
    ) {
    }

    /**
     * @param  array<string, mixed>  $settings  all settings, cast
     */
    public static function make(array $settings, SnmpCommunitySecretData|SnmpV3SecretData|null $secret): self
    {
        $community = $secret instanceof SnmpCommunitySecretData ? $secret : null;
        $v3 = $secret instanceof SnmpV3SecretData ? $secret : null;

        return new self(
            version: self::version($v3 ? SecretType::SnmpV3 : SecretType::SnmpCommunity, $settings['version'] ?? null),
            community: $community?->community,
            authname: $v3?->authname,
            authpass: $v3?->authpass,
            authlevel: $v3?->authlevel,
            authalgo: $v3?->authalgo,
            cryptopass: $v3?->cryptopass,
            cryptoalgo: $v3?->cryptoalgo,
            transport: $settings['transport'],
            port: $settings['port'],
            context: $settings['context'],
            timeout: $settings['timeout'],
            retries: $settings['retries'],
            maxRepeaters: $settings['max_repeaters'],
            maxOid: $settings['max_oid'],
            bulk: $settings['bulk'],
            portAssociationMode: $settings['port_association_mode'],
        );
    }

    /**
     * The SNMP version: a v3 secret is v3, a community is v2c unless the version setting is v1.
     */
    public static function version(?SecretType $secretType, ?string $version): string
    {
        if ($secretType === SecretType::SnmpV3) {
            return 'v3';
        }

        return $version === 'v1' ? 'v1' : 'v2c';
    }
}
