<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;
use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;

/**
 * The settings of a polling method: form fields, validation rules and how values are cast.
 * Each field key must match a property of the method's config (max_oid => maxOid).
 * Only values the user sets are stored, empty fields fall back to the config defaults at runtime.
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
        return collect($this->fields())->map(function (FieldDefinition $field, string $key) use ($defaults, $dataVar): array {
            $schemaField = $field->toSchemaField($dataVar);
            unset($schemaField['default']); // empty means default, never preselect it
            $default = $defaults->setting($key);

            if ($field->type === 'select') {
                if ($field->placeholder !== null) {
                    $schemaField['default_option'] = __($field->placeholder);
                } elseif ($default === null || $default === '') {
                    $schemaField['default_option'] = __('Default');
                } else {
                    $schemaField['default_option'] = __('Default (:value)', ['value' => $field->options[$default] ?? $default]);
                }
            } elseif (! isset($schemaField['placeholder']) && $default !== null && $default !== '') {
                $schemaField['placeholder'] = (string) $default;
            }

            return $schemaField;
        })->values()->all();
    }

    /**
     * The settings that are set, cast to their type. Empty and out of range values are left to the defaults.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function filterOverrides(array $input): array
    {
        $settings = [];

        foreach ($this->fields() as $key => $field) {
            $value = $input[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            $value = $field->castValue($value);
            if ($value === null
                || ($field->min !== null && $value < $field->min)
                || ($field->max !== null && $value > $field->max)) {
                continue;
            }

            $settings[$key] = $value;
        }

        return $settings;
    }
}
