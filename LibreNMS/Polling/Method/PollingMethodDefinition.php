<?php

/**
 * PollingMethodDefinition.php
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

namespace LibreNMS\Polling\Method;

use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Definitions\IcmpPollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\Definitions\IpmiPollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\Definitions\SnmpPollingMethodDefinitionInterface;
use LibreNMS\Polling\Method\Definitions\UnixAgentPollingMethodDefinitionInterface;

class PollingMethodDefinition
{
    /**
     * @return \LibreNMS\Interfaces\PollingMethodDefinitionInterface<\LibreNMS\Interfaces\PollingMethodInterface>
     */
    public static function for(PollingMethodType $type): \LibreNMS\Interfaces\PollingMethodDefinitionInterface
    {
        return match($type) {
            PollingMethodType::Snmp => new SnmpPollingMethodDefinitionInterface,
            PollingMethodType::Icmp => new IcmpPollingMethodDefinitionInterface,
            PollingMethodType::Ipmi => new IpmiPollingMethodDefinitionInterface,
            PollingMethodType::UnixAgent => new UnixAgentPollingMethodDefinitionInterface,
        };
    }

    public static function hasSecret(PollingMethodType $type): bool
    {
        return self::for($type)->secretClass() !== null;
    }

    /**
     * @param  array<string, array<string, mixed>>  $schema
     * @param  string  $dataVar
     * @return array<int, array<string, mixed>>
     */
    public static function buildSchemaFields(array $schema, string $dataVar = 'formData'): array
    {
        return collect($schema)->map(function (array $field, string $key) use ($dataVar): array {
            $visibleIfExpression = null;

            if (isset($field['visible_if']) && is_array($field['visible_if'])) {
                $visibleIfExpression = collect($field['visible_if'])
                    ->map(function (mixed $condVal, string $condKey): string {
                        if (is_array($condVal) && isset($condVal['$in'])) {
                            return json_encode(array_values($condVal['$in'])) . '.includes(__DATA_VAR__[' . json_encode($condKey) . '])';
                        }

                        return '__DATA_VAR__[' . json_encode($condKey) . '] === ' . json_encode($condVal);
                    })->implode(' && ');

                $visibleIfExpression = str_replace('__DATA_VAR__', $dataVar, $visibleIfExpression);
            }

            return [
                ...$field,
                'key' => $key,
                'field_type' => $field['type'] ?? 'text',
                'visible_if_expression' => $visibleIfExpression,
            ];
        })->values()->all();
    }
}
