<?php

namespace LibreNMS\Traits;

trait HandlesFieldSchema
{
    /**
     * Computed field defaults derived from schema.
     *
     * @return array<string, mixed>
     */
    public function schemaDefaults(): array
    {
        return collect($this->schema())
            ->mapWithKeys(fn (array $field, string $key): array => [
                $key => $field['default'] ?? (isset($field['options']) ? array_key_first($field['options']) : null),
            ])
            ->filter(fn (mixed $v): bool => $v !== null)
            ->all();
    }

    /**
     * Resolve field values by combining schema defaults, existing values, and new input values, filtering allowed schema keys.
     *
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function resolveValues(array $input, array $existing = []): array
    {
        $schema = $this->schema();
        if (empty($schema)) {
            return [];
        }

        $base = array_merge($this->schemaDefaults(), $existing);

        $allowedKeys = array_keys($schema);
        $filteredInput = collect($input)->only($allowedKeys)->filter(fn (mixed $v): bool => $v !== null)->all();

        return array_merge($base, $filteredInput);
    }

    /**
     * Build UI schema fields derived from schema().
     *
     * @param  array<string, array<string, mixed>>|null  $schema
     * @param  string  $dataVar
     * @return array<int, array<string, mixed>>
     */
    public function buildSchemaFields(?array $schema = null, string $dataVar = 'formData'): array
    {
        $targetSchema = $schema ?? $this->schema();

        return collect($targetSchema)->map(function (array $field, string $key) use ($dataVar): array {
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
