<?php

/**
 * SnmpPollingMethodDefinition.php
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

use App\Facades\LibrenmsConfig;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\SnmpPollingMethod;
use LibreNMS\Polling\Secrets\Definitions\SnmpSecretDefinition;

/**
 * @implements PollingMethodDefinitionInterface<SnmpPollingMethod>
 */
class SnmpPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public function schema(): array
    {
        return [
            'transport' => [
                'type' => 'select',
                'options' => [
                    'udp' => 'UDP',
                    'tcp' => 'TCP',
                    'udp6' => 'UDP6',
                    'tcp6' => 'TCP6',
                ],
            ],
            'port' => [
                'type' => 'number',
            ],
            'timeout' => [
                'type' => 'number',
            ],
            'retries' => [
                'type' => 'number',
            ],
            'max_repeaters' => [
                'type' => 'number',
            ],
            'max_oid' => [
                'type' => 'number',
            ],
            'port_association_mode' => [
                'type' => 'select',
                'options' => array_combine(PortAssociationMode::getModes(), PortAssociationMode::getModes()),
                'default' => LibrenmsConfig::get('default_port_association_mode', 'ifIndex'),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return [
            'affects_availability' => true,
            'transport' => 'default',
            'port' => 161,
            'timeout' => 3,
            'retries' => 1,
            'max_repeaters' => 0,
            'max_oid' => 10,
            'port_association_mode' => LibrenmsConfig::get('default_port_association_mode', 'ifIndex'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'transport' => ['required', 'string', 'in:udp,tcp,udp6,tcp6'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:60'],
            'retries' => ['nullable', 'integer', 'min:0', 'max:10'],
            'max_repeaters' => ['nullable', 'integer', 'min:0', 'max:30'],
            'max_oid' => ['nullable', 'integer', 'min:1', 'max:100'],
            'port_association_mode' => ['nullable', 'string', Rule::in(PortAssociationMode::getModes())],
        ];
    }

    /**
     * @inheritDoc
     */
    public function icon(): string
    {
        return 'fa-server';
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return SnmpPollingMethod::class;
    }

    /**
     * @inheritDoc
     */
    public function secretDefinition(): SnmpSecretDefinition
    {
        return new SnmpSecretDefinition;
    }
}
