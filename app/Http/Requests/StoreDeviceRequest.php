<?php

namespace App\Http\Requests;

use App\Models\Device;
use App\Models\PollerGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Device::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(\LibreNMS\Polling\Method\PollingMethodRegistry $registry): array
    {
        $rules = [
            'hostname' => ['required', 'ip_or_hostname'],
            'display_template' => ['nullable', 'string', 'max:128'],
            'poller_group' => ['nullable', 'integer', Rule::in(PollerGroup::pluck('id')->prepend(0))],
            'force_add' => ['nullable', 'boolean'],
            'polling_methods' => ['required', 'array', 'min:1'],
            'sysName' => ['nullable', 'string', 'max:255'],
            'hardware' => ['nullable', 'string', 'max:255'],
            'os' => ['nullable', 'string', 'max:255'],
        ];

        // Loop over the methods provided in the request
        foreach ($this->input('polling_methods', []) as $method => $data) {
            $type = PollingMethodType::tryFrom($method);
            if (! $type) {
                continue;
            }

            // Only validate if explicitly checked/enabled in form
            $isActive = ! empty($data['active']) && $this->boolean("polling_methods.{$method}.active");
            $isEnabled = ! empty($data['enabled']) && $this->boolean("polling_methods.{$method}.enabled");

            if (! $isActive && ! $isEnabled) {
                continue;
            }

            $rules["polling_methods.{$method}.active"] = ['nullable', 'boolean'];
            $rules["polling_methods.{$method}.validate"] = ['nullable', 'boolean'];
            $rules["polling_methods.{$method}.affects_availability"] = ['nullable', 'boolean'];
            $rules["polling_methods.{$method}.credential_mode"] = ['nullable', 'in:default,existing,new'];

            $pollingMethod = $registry->get($type);
            if (! $pollingMethod) {
                continue;
            }
            $secretDefinition = SecretDefinition::for($pollingMethod->secretType());
            if ($secretDefinition !== null) {
                $rules["polling_methods.{$method}.secret_id"] = [
                    'required_if:polling_methods.' . $method . '.credential_mode,existing',
                    'nullable',
                    'integer',
                    'exists:secrets,id',
                ];

                $rules["polling_methods.{$method}.description"] = ['nullable', 'string', 'max:255', 'unique:secrets,description'];

                $credentialMode = $data['credential_mode'] ?? 'default';
                if ($credentialMode === 'new') {
                    foreach ($secretDefinition->rules() as $key => $rule) {
                        $rules["polling_methods.{$method}.secret_data.{$key}"] = $rule;
                    }
                }
            }

            // Settings validation rules
            $rules["polling_methods.{$method}.settings"] = ['nullable', 'array'];
            foreach ($registry->definition($type)->rules() as $key => $rule) {
                $rules["polling_methods.{$method}.settings.{$key}"] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'force_add' => $this->boolean('force_add'),
        ]);

        // Merge flags in polling_methods
        $methods = $this->input('polling_methods', []);
        foreach ($methods as $method => $data) {
            if (isset($data['active'])) {
                $methods[$method]['active'] = $this->boolean("polling_methods.{$method}.active");
            }
            if (isset($data['enabled'])) {
                $methods[$method]['enabled'] = $this->boolean("polling_methods.{$method}.enabled");
            }
            if (isset($data['validate'])) {
                $methods[$method]['validate'] = $this->boolean("polling_methods.{$method}.validate");
            }
            if (isset($data['affects_availability'])) {
                $methods[$method]['affects_availability'] = $this->boolean("polling_methods.{$method}.affects_availability");
            }
        }
        $this->merge(['polling_methods' => $methods]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'polling_methods.required' => __('At least one polling method is required'),
            'polling_methods.min' => __('At least one polling method is required'),
        ];
    }
}
