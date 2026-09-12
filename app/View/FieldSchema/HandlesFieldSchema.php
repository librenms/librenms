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
     * Get field definitions for this schema.
     * Override in implementing classes.
     *
     * @return array<string, FieldDefinition>
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * UI/form schema derived from fields().
     *
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
     * Validation rules derived from fields().
     *
     * @return array<string, array<mixed>|string>
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
     * Computed field defaults derived from fields().
     *
     * @return array<string, mixed>
     */
    public function schemaDefaults(): array
    {
        return collect($this->fields())
            ->mapWithKeys(function (FieldDefinition $field, string $key): array {
                $val = $field->getDefault();

                return [$key => $val];
            })
            ->filter(fn (mixed $v): bool => $v !== null)
            ->all();
    }

    /**
     * Initial form values: select fields preselect their default, while text/number fields stay empty.
     *
     * @return array<string, mixed>
     */
    public function formDefaults(): array
    {
        return collect($this->fields())
            ->filter(fn (FieldDefinition $field): bool => $field->type === 'select')
            ->mapWithKeys(function (FieldDefinition $field, string $key): array {
                $val = $field->getDefault();

                return [$key => $val !== null ? (string) $val : null];
            })
            ->filter(fn (mixed $v): bool => $v !== null)
            ->all();
    }

    /**
     * Resolve field values by combining schema defaults, existing values, and new input values, filtering allowed schema keys and casting values.
     *
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function resolveValues(array $input, array $existing = []): array
    {
        $fields = $this->fields();
        if (empty($fields)) {
            return [];
        }

        $result = [];
        foreach ($fields as $key => $field) {
            if (array_key_exists($key, $input) && $input[$key] !== null && $input[$key] !== '') {
                $result[$key] = $field->castValue($input[$key]);
            } elseif (array_key_exists($key, $existing) && $existing[$key] !== null && $existing[$key] !== '') {
                $result[$key] = $field->castValue($existing[$key]);
            } elseif (($default = $field->getDefault()) !== null) {
                $result[$key] = $default;
            }
        }

        return $result;
    }

    /**
     * Filter input values for storage, retaining only non-empty values that differ from defaults.
     *
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function filterOverrides(array $input, array $existing = []): array
    {
        $fields = $this->fields();
        if (empty($fields)) {
            return [];
        }

        $result = [];
        foreach ($fields as $key => $field) {
            $default = $field->getDefault();

            if (array_key_exists($key, $input)) {
                $raw = $input[$key];
                if ($raw === null || $raw === '') {
                    continue;
                }
                $cast = $field->castValue($raw);
                if ($cast !== $default) {
                    $result[$key] = $cast;
                }
            } elseif (array_key_exists($key, $existing)) {
                $raw = $existing[$key];
                if ($raw === null || $raw === '') {
                    continue;
                }
                $cast = $field->castValue($raw);
                if ($cast !== $default) {
                    $result[$key] = $cast;
                }
            }
        }

        return $result;
    }

    /**
     * Build UI schema fields derived from fields().
     *
     * @param  array<string, array<string, mixed>>|null  $schema
     * @param  string  $dataVar
     * @return array<int, array<string, mixed>>
     */
    public function buildSchemaFields(?array $schema = null, string $dataVar = 'formData'): array
    {
        if ($schema !== null) {
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

        return collect($this->fields())
            ->map(fn (FieldDefinition $field): array => $field->toSchemaField($dataVar))
            ->values()
            ->all();
    }
}
