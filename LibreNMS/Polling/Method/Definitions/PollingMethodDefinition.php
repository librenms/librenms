<?php

namespace LibreNMS\Polling\Method\Definitions;

use App\View\FieldSchema\FieldDefinition;
use App\View\FieldSchema\HandlesFieldSchema;
use App\View\FieldSchema\HasFieldSchema;

/**
 * The settings of a polling method: form fields, validation rules and how values are cast.
 * Only values the user sets are stored (already cast), empty fields fall back to the defaults at runtime.
 */
abstract class PollingMethodDefinition implements HasFieldSchema
{
    use HandlesFieldSchema;

    abstract public function icon(): string;

    /**
     * Form fields that show the given defaults for fields left empty.
     *
     * @param  array<string, mixed>  $defaults  the polling method's default settings
     * @param  string|null  $secretTypeVar  JS expression for the type of the method's secret, null if unknown. Fields for other types are hidden
     * @return array<int, array<string, mixed>>
     */
    public function settingsFields(array $defaults, string $dataVar = 'settingsData', ?string $secretTypeVar = null): array
    {
        $fields = $this->fields();

        return collect($fields)->map(function (FieldDefinition $field, string $key) use ($fields, $defaults, $dataVar, $secretTypeVar): array {
            $schemaField = $field->toSchemaField($dataVar);
            unset($schemaField['default']); // empty means default, never preselect it
            $default = $defaults[$key] ?? null;

            if ($secretTypeVar !== null && $field->secretTypes !== null) {
                $forType = $this->forSecretTypesExpression($field, $secretTypeVar);
                $schemaField['visible_if_expression'] = $schemaField['visible_if_expression'] ? "{$schemaField['visible_if_expression']} && $forType" : $forType;
            }

            // a condition on a field that is not used with the secret type does not match
            if ($secretTypeVar !== null && $schemaField['fixed_if_expression'] !== null) {
                foreach (array_keys($field->fixedIf ?? []) as $conditionKey) {
                    if (isset($fields[$conditionKey]) && $fields[$conditionKey]->secretTypes !== null) {
                        $schemaField['fixed_if_expression'] .= ' && ' . $this->forSecretTypesExpression($fields[$conditionKey], $secretTypeVar);
                    }
                }
            }

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
     * JS expression that is true when the secret type is unknown or one the field is used with.
     */
    private function forSecretTypesExpression(FieldDefinition $field, string $secretTypeVar): string
    {
        return '(!' . $secretTypeVar . ' || ' . json_encode(array_column($field->secretTypes ?? [], 'value')) . '.includes(' . $secretTypeVar . '))';
    }

    /**
     * The values of the form fields for stored settings, empty for those not set.
     * Select options are strings, so a stored bool has to become its option to be selected.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function formValues(array $settings): array
    {
        return array_map(
            fn (mixed $value): mixed => is_bool($value) ? ($value ? '1' : '0') : $value,
            [...array_fill_keys(array_keys($this->fields()), ''), ...$settings],
        );
    }

    /**
     * The settings that are set, cast to their type. Empty, out of range and unknown option values are left to the defaults.
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

            if ($field->type === 'select' && ! empty($field->options)
                && ! in_array($value, array_map($field->castValue(...), array_keys($field->options)), true)) {
                continue;
            }

            $settings[$key] = $value;
        }

        return $settings;
    }
}
