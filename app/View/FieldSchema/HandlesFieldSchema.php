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
