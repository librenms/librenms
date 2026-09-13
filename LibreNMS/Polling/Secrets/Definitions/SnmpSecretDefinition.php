<?php

/**
 * SnmpSecretDefinition.php
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
use LibreNMS\Polling\Secrets\Data\SnmpSecretData;

/**
 * @implements SecretDefinitionInterface<SnmpSecretData>
 */
class SnmpSecretDefinition implements SecretDefinitionInterface
{
    use HandlesFieldSchema;

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'version' => FieldDefinition::make('version', 'select')
                ->label('SNMP Version')
                ->options([
                    'v1' => 'v1',
                    'v2c' => 'v2c',
                    'v3' => 'v3',
                ])
                ->default('v2c')
                ->rules(['required', 'in:v1,v2c,v3']),

            'community' => FieldDefinition::make('community', 'password')
                ->label('Community')
                ->visibleIf([
                    'version' => ['$in' => ['v1', 'v2c']],
                ])
                ->rules(['required_if:version,v1,v2c', 'string', 'nullable']),

            'authname' => FieldDefinition::make('authname', 'text')
                ->label('Auth Name')
                ->visibleIf([
                    'version' => 'v3',
                ])
                ->rules(['required_if:version,v3', 'string', 'nullable']),

            'authlevel' => FieldDefinition::make('authlevel', 'select')
                ->label('Auth Level')
                ->options([
                    'noAuthNoPriv' => 'No Authentication, No Privacy',
                    'authNoPriv' => 'Authentication, No Privacy',
                    'authPriv' => 'Authentication, Privacy',
                ])
                ->default('noAuthNoPriv')
                ->visibleIf([
                    'version' => 'v3',
                ])
                ->rules(['required_if:version,v3', 'in:noAuthNoPriv,authNoPriv,authPriv']),

            'authpass' => FieldDefinition::make('authpass', 'password')
                ->label('Auth Password')
                ->visibleIf([
                    'version' => 'v3',
                    'authlevel' => ['$in' => ['authNoPriv', 'authPriv']],
                ])
                ->rules(['required_if:authlevel,authNoPriv,authPriv', 'string', 'nullable']),

            'authalgo' => FieldDefinition::make('authalgo', 'select')
                ->label('Auth Algorithm')
                ->options([
                    'MD5' => 'MD5',
                    'SHA' => 'SHA',
                    'SHA-224' => 'SHA-224',
                    'SHA-256' => 'SHA-256',
                    'SHA-384' => 'SHA-384',
                    'SHA-512' => 'SHA-512',
                ])
                ->default('SHA')
                ->visibleIf([
                    'version' => 'v3',
                    'authlevel' => ['$in' => ['authNoPriv', 'authPriv']],
                ])
                ->rules(['required_if:authlevel,authNoPriv,authPriv', 'in:MD5,SHA,SHA-224,SHA-256,SHA-384,SHA-512']),

            'cryptopass' => FieldDefinition::make('cryptopass', 'password')
                ->label('Crypto Password')
                ->visibleIf([
                    'version' => 'v3',
                    'authlevel' => 'authPriv',
                ])
                ->rules(['required_if:authlevel,authPriv', 'string', 'nullable']),

            'cryptoalgo' => FieldDefinition::make('cryptoalgo', 'select')
                ->label('Crypto Algorithm')
                ->options([
                    'DES' => 'DES',
                    'AES' => 'AES',
                    'AES-192' => 'AES-192',
                    'AES-256' => 'AES-256',
                    'AES-192-C' => 'AES-192-C',
                    'AES-256-C' => 'AES-256-C',
                ])
                ->default('AES')
                ->visibleIf([
                    'version' => 'v3',
                    'authlevel' => 'authPriv',
                ])
                ->rules(['required_if:authlevel,authPriv', 'in:DES,AES,AES-192,AES-256,AES-192-C,AES-256-C']),

            'context' => FieldDefinition::make('context', 'text')
                ->label('Context')
                ->visibleIf([
                    'version' => 'v3',
                ])
                ->rules(['nullable', 'string']),
        ];
    }

    public function type(): SecretType
    {
        return SecretType::Snmp;
    }

    /**
     * @inheritDoc
     */
    public function class(): string
    {
        return SnmpSecretData::class;
    }
}
