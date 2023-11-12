<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            "hostname" => "required|string|max:255",
            "port" => "nullable|integer|min:1|max:65535",
            "transport" => "nullable|string|max:255",
            "snmpver" => "required|string|max:255",
            "community" => "nullable|string|max:255",
            "authlevel" => "nullable|string|max:255",
            "authname" => "nullable|string|max:255",
            "authpass" => "nullable|string|max:255",
            "authalgo" => "nullable|string|max:255",
            "cryptopass" => "nullable|string|max:255",
            "cryptoalgo" => "nullable|string|max:255",
            "os" => "nullable|string|max:255",
            "hardware" => "nullable|string|max:255",
            "sysName" => "nullable|string|max:255",
            "poller_group" => "nullable|string|max:255",
            "port_assoc_mode" => "nullable|string|max:255",
            "force_add" => "nullable|string|max:255",
        ];
    }
}
