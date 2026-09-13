<?php

/**
 * IpmiSecretDefinition.php
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

namespace LibreNMS\Polling\Secrets\Definitions;

use App\View\FieldSchema\FieldDefinition;
use App\View\FieldSchema\HandlesFieldSchema;
use LibreNMS\Enum\SecretType;
use LibreNMS\Interfaces\SecretDefinitionInterface;
use LibreNMS\Polling\Secrets\Data\IpmiSecretData;

/**
 * @implements SecretDefinitionInterface<IpmiSecretData>
 */
class IpmiSecretDefinition implements SecretDefinitionInterface
{
    use HandlesFieldSchema;

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'username' => FieldDefinition::make('username', 'text')
                ->label('Username')
                ->rules(['nullable', 'string']),

            'password' => FieldDefinition::make('password', 'password')
                ->label('Password')
                ->rules(['nullable', 'string']),

            'kg_key' => FieldDefinition::make('kg_key', 'password')
                ->label('KG/BMC Key')
                ->rules(['nullable', 'string', 'size:40', 'regex:/^[a-fA-F0-9]+$/']),
        ];
    }

    public function type(): SecretType
    {
        return SecretType::Ipmi;
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return IpmiSecretData::class;
    }
}
