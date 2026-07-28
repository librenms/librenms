<?php

/**
 * AbstractPollingMethodDefinition.php
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

use LibreNMS\Interfaces\PollingMethodDefinitionInterface;
use LibreNMS\Interfaces\PollingMethodInterface;

/**
 * @template-covariant T of PollingMethodInterface
 *
 * @implements PollingMethodDefinitionInterface<T>
 */
abstract class AbstractPollingMethodDefinition implements PollingMethodDefinitionInterface
{
    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function resolveSettings(array $input, array $existing = []): array
    {
        /** @var array<string, array<string, mixed>> $schema */
        $schema = $this->schema();
        if (empty($schema)) {
            return [];
        }

        $schemaDefaults = collect($schema)
            ->mapWithKeys(fn (array $field, string $key): array => [
                $key => $field['default'] ?? (isset($field['options']) ? array_key_first($field['options']) : null),
            ])
            ->filter(fn (mixed $v): bool => $v !== null)
            ->all();

        /** @var array<string, mixed> $defaults */
        $defaults = $this->defaults();
        $baseDefaults = collect($defaults)->except('affects_availability')->all();

        $base = array_merge($schemaDefaults, $baseDefaults, $existing);

        $allowedKeys = array_keys($schema);
        $filteredInput = collect($input)->only($allowedKeys)->all();

        return array_merge($base, $filteredInput);
    }
}
