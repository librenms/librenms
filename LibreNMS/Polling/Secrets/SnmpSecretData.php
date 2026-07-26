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

namespace LibreNMS\Polling\Secrets;

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

    public static function fromArray(array $data): static
    {
        return new static(
            version: $data['version'] ?? 'v2c',
            community: $data['community'] ?? null,
            authname: $data['authname'] ?? null,
            authpass: $data['authpass'] ?? null,
            authlevel: $data['authlevel'] ?? 'noAuthNoPriv',
            authalgo: $data['authalgo'] ?? 'SHA',
            cryptopass: $data['cryptopass'] ?? null,
            cryptoalgo: $data['cryptoalgo'] ?? 'AES',
            context: $data['context'] ?? null,
        );
    }

    /**
     * @deprecated
     *
     * @param  array  $device
     * @return static
     */
    public static function fromDeviceArray(array $device): static
    {
        return new static(
            version: $device['snmpver'] ?? 'v2c',
            community: $device['community'] ?? null,
            authname: $device['authname'] ?? null,
            authpass: $device['authpass'] ?? null,
            authlevel: $device['authlevel'] ?? 'noAuthNoPriv',
            authalgo: $device['authalgo'] ?? 'SHA',
            cryptopass: $device['cryptopass'] ?? null,
            cryptoalgo: $device['cryptoalgo'] ?? 'AES',
            context: $device['context'] ?? null,
        );
    }

    public static function rules(): array
    {
        return [
            'version' => 'required|in:v1,v2c,v3',
            'community' => 'required_if:version,v1,v2c|string|nullable',
            'authname' => 'required_if:version,v3|string|nullable',
            'authpass' => 'required_if:authlevel,authNoPriv,authPriv|string|nullable',
            'authlevel' => 'required_if:version,v3|in:noAuthNoPriv,authNoPriv,authPriv',
            'authalgo' => 'required_if:authlevel,authNoPriv,authPriv|in:MD5,SHA,SHA-224,SHA-256,SHA-384,SHA-512',
            'cryptopass' => 'required_if:authlevel,authPriv|string|nullable',
            'cryptoalgo' => 'required_if:authlevel,authPriv|in:DES,AES,AES-192,AES-256,AES-192-C,AES-256-C',
        ];
    }

    public static function getUiSchema(): array
    {
        return [
            'version' => [
                'type' => 'select',
                'label' => 'SNMP Version',
                'options' => [
                    'v1' => 'v1',
                    'v2c' => 'v2c',
                    'v3' => 'v3',
                ],
            ],
            'community' => [
                'type' => 'password',
                'label' => 'Community',
                'visible_if' => [
                    'version' => ['$in' => ['v1', 'v2c']],
                ],
            ],
            'authname' => [
                'type' => 'text',
                'label' => 'Auth Name',
                'visible_if' => [
                    'version' => 'v3',
                ],
            ],
            'authlevel' => [
                'type' => 'select',
                'label' => 'Auth Level',
                'options' => [
                    'noAuthNoPriv' => 'No Authentication, No Privacy',
                    'authNoPriv' => 'Authentication, No Privacy',
                    'authPriv' => 'Authentication, Privacy',
                ],
                'visible_if' => [
                    'version' => 'v3',
                ],
            ],
            'authpass' => [
                'type' => 'password',
                'label' => 'Auth Password',
                'visible_if' => [
                    'version' => 'v3',
                    'authlevel' => ['$in' => ['authNoPriv', 'authPriv']],
                ],
            ],
            'authalgo' => [
                'type' => 'select',
                'label' => 'Auth Algorithm',
                'options' => [
                    'MD5' => 'MD5',
                    'SHA' => 'SHA',
                    'SHA-224' => 'SHA-224',
                    'SHA-256' => 'SHA-256',
                    'SHA-384' => 'SHA-384',
                    'SHA-512' => 'SHA-512',
                ],
                'visible_if' => [
                    'version' => 'v3',
                    'authlevel' => ['$in' => ['authNoPriv', 'authPriv']],
                ],
            ],
            'cryptopass' => [
                'type' => 'password',
                'label' => 'Crypto Password',
                'visible_if' => [
                    'version' => 'v3',
                    'authlevel' => 'authPriv',
                ],
            ],
            'cryptoalgo' => [
                'type' => 'select',
                'label' => 'Crypto Algorithm',
                'options' => [
                    'DES' => 'DES',
                    'AES' => 'AES',
                    'AES-192' => 'AES-192',
                    'AES-256' => 'AES-256',
                    'AES-192-C' => 'AES-192-C',
                    'AES-256-C' => 'AES-256-C',
                ],
                'visible_if' => [
                    'version' => 'v3',
                    'authlevel' => 'authPriv',
                ],
            ],
            'context' => [
                'type' => 'text',
                'label' => 'Context',
                'visible_if' => [
                    'version' => 'v3',
                ],
            ],
        ];
    }
}
