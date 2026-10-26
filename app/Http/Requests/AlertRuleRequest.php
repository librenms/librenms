<?php

/**
 * AlertRuleRequest.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Requests;

use App\Models\AlertOperation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Validation\ValidationException;
use LibreNMS\Alert\KeyValueOptions;

class AlertRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Route is protected by can:admin middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::when($this->route()->getActionMethod() === 'store', Rule::unique((app()->environment('testing') ? 'testing.' : '') . 'alert_rules', 'name'))],
            'severity' => ['required', 'string', 'max:32'],

            'override_query' => ['sometimes', 'boolean'],
            'builder_json' => ['required_unless:override_query,on', 'json'],
            'adv_query' => ['required_if:override_query,on', 'nullable', 'string'],

            'invert' => ['sometimes', 'boolean'],
            'recovery' => ['sometimes', 'boolean'],
            'mute' => ['sometimes', 'boolean'],
            'acknowledgement' => ['sometimes', 'boolean'],
            'invert_map' => ['sometimes', 'boolean'],
            'notify_per_entity' => ['sometimes', 'boolean'],
            'max_entities' => ['sometimes', 'nullable', 'integer', 'min:1'],

            'maps' => ['sometimes', 'array'],
            'maps.*' => ['string'],

            'alert_operation_id' => ['nullable', 'integer', Rule::exists(AlertOperation::class, 'id')],

            'proc' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ] + self::customAttributeRules();
    }

    /** @return array<string, mixed> */
    public static function customAttributeRules(): array
    {
        return [
            'custom_attributes' => ['sometimes', 'array', 'max:50', new class implements ValidationRule
            {
                public bool $implicit = true;

                public function validate(string $attribute, mixed $value, \Closure $fail): void
                {
                    if (! is_array($value)) {
                        $fail('Attributes must be a map of names to string values.');

                        return;
                    }
                    foreach ($value as $name => $entry) {
                        if (! is_string($name) || strlen($name) > 64 || ! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name)) {
                            $fail('Attribute names must start with a letter or underscore, contain only letters, numbers and underscores, and be at most 64 characters.');
                        }
                        if (! is_string($entry) || mb_strlen($entry) > 4096) {
                            $fail('Attribute values must be strings of at most 4096 characters.');
                        }
                    }
                }
            }],
            'custom_attributes.*' => ['string', 'max:4096'],
        ];
    }

    /**
     * Sanitize/normalize inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->exists('custom_attributes-rows')) {
            $rows = $this->input('custom_attributes-rows') ?? [];
            $rows = $rows === '' ? [] : $rows;
            if (! is_array($rows)) {
                throw ValidationException::withMessages(['custom_attributes' => 'Attributes must be name/value rows.']);
            }
            // Empty form inputs pass through ConvertEmptyStringsToNull middleware.
            foreach ($rows as &$row) {
                if (is_array($row)) {
                    foreach (['name', 'value'] as $field) {
                        if (array_key_exists($field, $row) && $row[$field] === null) {
                            $row[$field] = '';
                        }
                    }
                }
            }
            unset($row);
            try {
                $this->merge(['custom_attributes' => KeyValueOptions::fromRows($rows)]);
            } catch (\InvalidArgumentException $e) {
                throw ValidationException::withMessages(['custom_attributes' => $e->getMessage()]);
            }
        }

        $this->merge([
            'name' => isset($this->name) ? strip_tags((string) $this->name) : $this->name,
            'notes' => isset($this->notes) ? strip_tags((string) $this->notes) : $this->notes,
        ]);

        // Convert checkbox values ('on' string) to boolean
        $this->merge(collect(['invert', 'recovery', 'mute', 'acknowledgement', 'invert_map', 'notify_per_entity', 'override_query'])
            ->mapWithKeys(fn ($field) => [$field => match ($this->input($field)) {
                'on', '1', 1, true => true,
                default => false
            }])->all());

        // Ensure maps is array if present as empty string
        foreach (['maps'] as $key) {
            $value = $this->input($key);
            if ($value === '' || $value === null) {
                $this->merge([$key => []]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $invertMap = filter_var($this->input('invert_map'), FILTER_VALIDATE_BOOLEAN);
            if ($invertMap) {
                $maps = $this->input('maps');
                if (empty($maps) || ! is_array($maps)) {
                    $v->errors()->add('maps', 'Invert map is on but no selection in devices, groups and locations match list');
                }
            }
        });
    }
}
