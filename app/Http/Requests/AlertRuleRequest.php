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
            'name' => ['required', 'string', 'max:255', Rule::when($this->route()->getActionMethod() === 'store', Rule::unique((app()->environment('testing') ? 'testing.' : '') . 'alert_rules', 'name'))],
            'severity' => ['required', 'string', 'max:32'],

            'override_query' => ['sometimes', 'boolean'],
            'builder_json' => ['required_unless:override_query,on', 'json'],
            'adv_query' => ['required_if:override_query,on', 'nullable', 'string'],

            'count' => ['nullable', 'numeric', 'min:-1'],
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

        // Convert checkbox values ('on' string) to boolean
        $this->merge(collect(['mute', 'invert', 'recovery', 'acknowledgement', 'invert_map', 'override_query'])
            ->mapWithKeys(fn ($field) => [$field => match ($this->input($field)) {
                'on', '1', 1, true => true,
                default => false
            }])->toArray());

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
