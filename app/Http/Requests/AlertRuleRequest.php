<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'rule_id' => ['nullable', 'integer', 'min:1', 'exists:alert_rules,id'],

            'name' => ['required', 'string', 'max:255', Rule::when($this->route()->getActionMethod() === 'store', Rule::unique((app()->environment('testing') ? 'testing.' : '') . 'alert_rules', 'name'))],
            'severity' => ['required', 'string', 'max:32'],

            'override_query' => ['nullable', Rule::in(['on'])],
            'builder_json' => ['required_unless:override_query,on', 'string'],
            'adv_query' => ['required_if:override_query,on', 'string'],

            'count' => ['nullable', 'numeric'],
            'delay' => ['nullable', 'string', 'regex:/^\d+[mhd]?$/'],
            'interval' => ['nullable', 'string', 'regex:/^\d+[mhd]?$/'],

            'mute' => ['sometimes', 'boolean'],
            'invert' => ['sometimes', 'boolean'],
            'recovery' => ['sometimes', 'boolean'],
            'acknowledgement' => ['sometimes', 'boolean'],
            'invert_map' => ['sometimes', 'boolean'],

            'maps' => ['sometimes', 'array'],
            'maps.*' => ['string'],

            'transports' => ['sometimes', 'array'],
            'transports.*' => ['string'],

            'proc' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Sanitize/normalize inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => isset($this->name) ? strip_tags((string) $this->name) : $this->name,
            'notes' => isset($this->notes) ? strip_tags((string) $this->notes) : $this->notes,
        ]);

        // Ensure maps/transports are arrays if present as empty string
        foreach (['maps', 'transports'] as $key) {
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
