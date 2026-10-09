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

use LibreNMS\Polling\Secrets\Data\SnmpSecretData;

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
    public static function make(array $settings, SnmpSecretData $secret): self
    {
        return new self(
            version: $secret->version,
            community: $secret->community,
            authname: $secret->authname,
            authpass: $secret->authpass,
            authlevel: $secret->authlevel,
            authalgo: $secret->authalgo,
            cryptopass: $secret->cryptopass,
            cryptoalgo: $secret->cryptoalgo,
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
}
