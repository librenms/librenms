<?php

/**
 * IpmiSecretData.php
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

class IpmiSecretData extends SecretData
{
    public function __construct(
        public ?string $username = null,
        public ?string $password = null,
        public ?string $kg_key = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
            kg_key: $data['kg_key'] ?? null,
        );
    }

    public static function rules(): array
    {
        return [
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'kg_key' => 'nullable|string|size:40|regex:/^[a-fA-F0-9]+$/',
        ];
    }

    public static function getUiSchema(): array
    {
        return [
            'username' => [
                'type' => 'text',
                'label' => 'Username',
            ],
            'password' => [
                'type' => 'password',
                'label' => 'Password',
            ],
            'kg_key' => [
                'type' => 'password',
                'label' => 'KG/BMC Key',
            ],
        ];
    }
}
