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
            'override_Oxidized_disable' => 'nullable|boolean',
            'override_device_ssh_port' => 'nullable|integer|between:1,65535',
            'override_device_telnet_port' => 'nullable|integer|between:1,65535',
            'override_device_http_port' => 'nullable|integer|between:1,65535',
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
            'override_Oxidized_disable' => $this->boolean('override_Oxidized_disable'),
        ]);
    }
}
