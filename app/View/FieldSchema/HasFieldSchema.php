<?php

namespace App\View\FieldSchema;

interface HasFieldSchema
{
    /**
     * Typed field definitions for this schema.
     *
     * @return array<string, \App\View\FieldSchema\FieldDefinition>
     */
    public function fields(): array;

    /**
     * UI/form schema for device-specific settings.
     *
     * @return array<string, array{type: string, default?: mixed, options?: array<string,string>, visible_if?: array<string, mixed>}>
     */
    public function schema(): array;

    /**
     * Build UI schema fields for rendering.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildSchemaFields(string $dataVar = 'formData'): array;

    /**
     * Validation rules for each field
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array;
}
