<?php

/**
 * SnmpSecretData.php
 *
 * -Description-
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

namespace LibreNMS\Polling\Secrets\Data;

use LibreNMS\Polling\Secrets\SecretData;

class SnmpSecretData extends SecretData
{
    public function __construct(
        public string $version = 'v2c',
        public ?string $community = null,
        public ?string $authname = null,
        public ?string $authpass = null,
        public string $authlevel = 'noAuthNoPriv',
        public string $authalgo = 'SHA',
        public ?string $cryptopass = null,
        public string $cryptoalgo = 'AES',
        public ?string $context = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $definition = new \LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;
        $resolved = $definition->resolveValues($data);

        return new static(
            version: $resolved['version'],
            community: $resolved['community'] ?? null,
            authname: $resolved['authname'] ?? null,
            authpass: $resolved['authpass'] ?? null,
            authlevel: $resolved['authlevel'],
            authalgo: $resolved['authalgo'],
            cryptopass: $resolved['cryptopass'] ?? null,
            cryptoalgo: $resolved['cryptoalgo'],
            context: $resolved['context'] ?? null,
        );
    }
}
