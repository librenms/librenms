<?php

/**
 * IpmiPollingMethodDefinition.php
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

namespace LibreNMS\Polling\Method\Definitions;

use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\IpmiPollingMethod;
use LibreNMS\Polling\Secrets\IpmiSecretData;

/**
 * @implements PollingMethodDefinitionInterface<IpmiPollingMethod>
 */
class IpmiPollingMethodDefinitionInterface implements PollingMethodDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public function schema(): array
    {
        return [
            'hostname' => [
                'type' => 'text',
            ],
            'port' => [
                'type' => 'number',
            ],
            'ciphersuite' => [
                'type' => 'text',
            ],
            'timeout' => [
                'type' => 'number',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return [
            'affects_availability' => false,
            'hostname' => '',
            'port' => 623,
            'ciphersuite' => '',
            'timeout' => 3,
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'hostname' => ['required', 'string'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'ciphersuite' => ['nullable', 'string'],
            'timeout' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function icon(): string
    {
        return 'fa-microchip';
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return IpmiPollingMethod::class;
    }

    /**
     * @inheritDoc
     */
    public function secretClass(): string
    {
        return IpmiSecretData::class;
    }
}
