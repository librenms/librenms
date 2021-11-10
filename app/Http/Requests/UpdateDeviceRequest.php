<?php

namespace App\Http\Requests;

use App\Facades\LibrenmsConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Enum\PollingMethodType;

class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->device);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hostname' => 'nullable|ip_or_hostname',
            'display_template' => 'nullable|string',
            'overwrite_ip' => 'nullable|ip',
            'purpose' => 'nullable|string',
            'type' => 'nullable|string',
            'parent_id' => 'nullable|array',
            'parent_id.*' => 'integer',
            'poller_group' => 'nullable|int',
            'override_sysLocation' => 'nullable|boolean',
            'sysLocation' => 'nullable|string',
            'override_sysContact' => 'nullable|boolean',
            'override_sysContact_string' => 'nullable|string',
            'disabled' => 'nullable|boolean',
            'disable_notify' => 'nullable|boolean',
            'ignore' => 'nullable|boolean',
            'ignore_status' => 'nullable|boolean',
            ...$this->withoutSnmp() ? [
                'sysName' => 'nullable|string|max:128',
                'hardware' => 'nullable|string|max:255',
                'os' => ['nullable', 'string', Rule::in(array_keys(LibrenmsConfig::get('os', [])))],
            ] : [],
        ];
    }

    /**
     * Without SNMP, these details are not discovered so the user may set them.
     */
    public function withoutSnmp(): bool
    {
        return $this->device->pollingMethod(PollingMethodType::Snmp) === null;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Handle boolean fields - ensure they return false when missing
        $this->merge([
            'override_sysLocation' => $this->boolean('override_sysLocation'),
            'override_sysContact' => $this->boolean('override_sysContact'),
            'disabled' => $this->boolean('disabled'),
            'disable_notify' => $this->boolean('disable_notify'),
            'ignore' => $this->boolean('ignore'),
            'ignore_status' => $this->boolean('ignore_status'),
            'type' => $this->input('type') ?? '',
        ]);
    }
}
