<?php

namespace App\Http\Requests;

use App\Models\Device;
use App\Models\PollerGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;

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
    public function rules(): array
    {
        $rules = [
            'hostname' => ['required', 'ip_or_hostname'],
            'port' => ['nullable', 'integer', 'between:1,65535'],
            'transport' => ['nullable', 'string', 'in:udp,udp6,tcp,tcp6'],
            'poller_group' => ['nullable', 'integer', Rule::in(PollerGroup::pluck('id')->prepend(0))],
            'port_assoc_mode' => ['nullable', 'string', Rule::in(PortAssociationMode::getModes())],
            'force_add' => ['nullable', 'boolean'],
            'ping_fallback' => ['nullable', 'boolean'],
            'polling_methods' => ['required', 'array'],
            'sysName' => ['nullable', 'string', 'max:255'],
            'hardware' => ['nullable', 'string', 'max:255'],
            'os' => ['nullable', 'string', 'max:255'],
            'active_tab' => ['nullable', 'string'],
            'active_methods' => ['nullable', 'array'],
            'active_methods.*' => ['string'],
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

            if ($type->hasSecret()) {
                $rules["polling_methods.{$method}.secret_id"] = [
                    'required_if:polling_methods.' . $method . '.credential_mode,existing',
                    'nullable',
                    'integer',
                    'exists:secrets,id',
                ];

                $rules["polling_methods.{$method}.description"] = ['nullable', 'string', 'max:255'];
                $rules["polling_methods.{$method}.default"] = ['nullable', 'boolean'];

                $credentialMode = $data['credential_mode'] ?? 'default';
                if ($credentialMode === 'new') {
                    $secretClass = $type->secretClass();
                    foreach ($secretClass::rules() as $key => $rule) {
                        $rules["polling_methods.{$method}.secret_data.{$key}"] = $rule;
                    }
                }
            }

            // Settings validation rules
            $methodClass = $type->methodClass();
            $rules["polling_methods.{$method}.settings"] = ['nullable', 'array'];
            foreach ($methodClass::getRules() as $key => $rule) {
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
            'ping_fallback' => $this->boolean('ping_fallback'),
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
            if (isset($data['default'])) {
                $methods[$method]['default'] = $this->boolean("polling_methods.{$method}.default");
            }
        }
        $this->merge(['polling_methods' => $methods]);
    }
}
