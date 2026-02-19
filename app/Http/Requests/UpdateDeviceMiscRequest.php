<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceMiscRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'override_icmp_disable' => 'nullable|boolean',
            'override_Oxidized_disable' => 'nullable|boolean',
            'override_device_ssh_port' => 'nullable|integer|between:1,65535',
            'override_device_telnet_port' => 'nullable|integer|between:1,65535',
            'override_device_http_port' => 'nullable|integer|between:1,65535',
            'override_Unixagent_port' => 'nullable|integer|between:1,65535',
            'override_rrdtool_tune' => 'nullable|boolean',
            'selected_ports' => 'nullable|boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'override_icmp_disable' => $this->boolean('override_icmp_disable'),
            'override_Oxidized_disable' => $this->boolean('override_Oxidized_disable'),
            'override_rrdtool_tune' => $this->boolean('override_rrdtool_tune'),
            'selected_ports' => $this->boolean('selected_ports'),
        ]);
    }
}
