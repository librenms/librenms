<?php

/**
 * HandlesFieldSchema.php
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

namespace App\View\FieldSchema;

trait HandlesFieldSchema
{
    /**
     * @return array<string, FieldDefinition>
     */
    abstract public function fields(): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function schema(): array
    {
        return collect($this->fields())
            ->mapWithKeys(fn (FieldDefinition $field, string $key): array => [
                $key => $field->toSchemaArray(),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return collect($this->fields())
            ->mapWithKeys(fn (FieldDefinition $field, string $key): array => [
                $key => $field->getRules(),
            ])
            ->filter(fn (mixed $rules): bool => ! empty($rules))
            ->all();
    }

    /**
     * Default value of each field. Override to source defaults from elsewhere.
     *
     * @return array<string, mixed>
     */
    public function schemaDefaults(): array
    {
        return collect($this->fields())
            ->map(fn (FieldDefinition $field): mixed => $field->getDefault())
            ->filter(fn (mixed $v): bool => $v !== null)
            ->all();
    }

    /**
     * Initial form values: select fields preselect their default, while text/number fields stay empty.
     *
     * @return array<string, string>
     */
    public function formDefaults(): array
    {
        $defaults = $this->schemaDefaults();

        return collect($this->fields())
            ->filter(fn (FieldDefinition $field, string $key): bool => $field->type === 'select' && isset($defaults[$key]))
            ->map(fn (FieldDefinition $field, string $key): string => (string) $defaults[$key])
            ->all();
    }

    /**
     * Reduce input to the values that differ from the defaults.
     * Keys missing from the input keep their existing value; empty values clear it.
     *
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function filterOverrides(array $input, array $existing = []): array
    {
        $defaults = $this->schemaDefaults();
        $result = [];

        foreach ($this->fields() as $key => $field) {
            $raw = array_key_exists($key, $input) ? $input[$key] : ($existing[$key] ?? null);
            if ($raw === null || $raw === '') {
                continue;
            }

            $value = $field->castValue($raw);
            $default = $defaults[$key] ?? null;
            $isDefault = (is_numeric($value) && is_numeric($default))
                ? (float) $value === (float) $default
                : $value === $default;

            if (! $isDefault) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Field data for rendering with the field-schema-fields component.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildSchemaFields(string $dataVar = 'formData'): array
    {
        return collect($this->fields())
            ->map(fn (FieldDefinition $field): array => $field->toSchemaField($dataVar))
            ->values()
            ->all();
    }
}
