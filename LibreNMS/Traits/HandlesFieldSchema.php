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
}
