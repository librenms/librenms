<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;
use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;

/**
 * UI description of a polling method's settings: form fields, validation rules and icon.
 * Only values the user sets are stored, empty fields fall back to the defaults at runtime.
 */
abstract class PollingMethodDefinition implements HasFieldSchema
{
    use HandlesFieldSchema;

    abstract public function icon(): string;

    /**
     * Form fields that show the given defaults for fields left empty.
     *
     * @return array<int, array<string, mixed>>
     */
    public function settingsFields(PollingMethodConfig $defaults, string $dataVar = 'settingsData'): array
    {
        $defaultValues = $defaults->settingsArray();

        return collect($this->fields())->map(function (FieldDefinition $field, string $key) use ($defaultValues, $dataVar): array {
            $schemaField = $field->toSchemaField($dataVar);
            unset($schemaField['default']); // empty means default, never preselect it
            $default = $defaultValues[$key] ?? null;

            if ($field->type === 'select') {
                $schemaField['default_option'] = $default === null ? __('Default') : __('Default (:value)', [
                    'value' => $field->options[$default] ?? $default,
                ]);
            } elseif (! isset($schemaField['placeholder']) && $default !== null && $default !== '') {
                $schemaField['placeholder'] = (string) $default;
            }

            return $schemaField;
        })->values()->all();
    }

    /**
     * The settings the user set, empty values are left to the defaults.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function filterOverrides(array $input): array
    {
        $settings = [];

        foreach ($this->fields() as $key => $field) {
            $value = $input[$key] ?? null;
            if ($value !== null && $value !== '') {
                $settings[$key] = $field->castValue($value);
            }
        }

        return $settings;
    }
}
