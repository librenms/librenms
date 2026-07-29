<?php

namespace LibreNMS\Interfaces;

interface HasFieldSchema
{
    /**
     * UI/form schema for device-specific settings.
     *
     * @return array<string, array{type: string, default?: mixed, options?: array<string,string>, visible_if?: array<string, mixed>}>
     */
    public function schema(): array;

    /**
     * Computed field defaults derived from schema.
     *
     * @return array<string, mixed>
     */
    public function schemaDefaults(): array;

    /**
     * Resolve field values by combining schema defaults, existing values, and new input values, filtering allowed schema keys.
     *
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function resolveValues(array $input, array $existing = []): array;

    /**
     * Build UI schema fields derived from schema().
     *
     * @param  array<string, array<string, mixed>>|null  $schema
     * @param  string  $dataVar
     * @return array<int, array<string, mixed>>
     */
    public function buildSchemaFields(?array $schema = null, string $dataVar = 'formData'): array;

    /**
     * Validation rules for polling method per-device settings
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array;
}
