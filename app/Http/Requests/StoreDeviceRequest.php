<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'hostname' => 'required|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'transport' => 'nullable|string|max:255',
            'snmpver' => ['nullable|string|max:255|', Rule::in(['v1', 'v2c', 'v3'])],
            'community' => 'required_if:snmpver,v2c|string|max:255',
            'authlevel' => 'required_if:snmpver:v3|string|max:255',
            'authname' => 'required_if:snmpver:v3|nullable|string|max:255',
            'authpass' => 'required_if:snmpver:v3|nullable|string|max:255',
            'authalgo' => 'nullable|string|max:255',
            'cryptopass' => 'nullable|string|max:255',
            'cryptoalgo' => 'nullable|string|max:255',
            'os' => 'nullable|string|max:255',
            'hardware' => 'nullable|string|max:255',
            'sysName' => 'nullable|string|max:255',
            'poller_group' => 'nullable|integer|max:255',
            'port_assoc_mode' => 'nullable|string|max:255',
            'force_add' => 'nullable|max:255',
        ];
    }
}
