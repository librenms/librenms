<?php

namespace App\Http\Requests;

use App\Services\BulkSnmpService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BulkSnmpRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized to make this request.
     * LibreNMS uses Spatie Laravel Permission; 'admin' is the privileged role.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->hasRole('admin');
    }

    /**
     * Provide a clear message when authorization fails.
     * The AJAX frontend surfaces this text to the user.
     */
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException(
            __('bulk-snmp.denied.message')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,mixed>
     */
    public function rules(): array
    {
        $rules = [
            'snmpver' => 'required|in:v1,v2c,v3',
            'port' => 'nullable|integer|between:1,65535',
            'transport' => 'nullable|in:udp,udp6,tcp,tcp6',
            'skip_down' => 'nullable|boolean',
        ];

        if ($this->input('snmpver') === 'v3') {
            $rules += [
                'authlevel' => 'required|in:' . implode(',', BulkSnmpService::SECURITY_LEVELS),
                'authname' => 'required|string|max:64',
                'authpass' => 'nullable|string|min:8|max:64',
                'authalgo' => 'required|in:' . implode(',', BulkSnmpService::AUTH_ALGOS),
                'cryptopass' => 'nullable|string|min:8|max:64',
                'cryptoalgo' => 'required|in:' . implode(',', BulkSnmpService::PRIV_ALGOS),
            ];
        } else {
            // v1 / v2c
            $rules['community'] = 'required|string|max:64';
        }

        return $rules;
    }

    /**
     * Custom messages for validator errors.
     *
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'authpass.min' => __('bulk-snmp.validation.password_min'),
            'cryptopass.min' => __('bulk-snmp.validation.password_min'),
        ];
    }
}
