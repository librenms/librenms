<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => [
                'required',
                'alpha_dash',
                'max:255',
                Rule::unique('users', 'username')->where('auth_type', LegacyAuth::getType()),
            ],
            'realname' => 'max:64',
            'email' => 'nullable|email|max:64',
            'descr' => 'max:30',
            'level' => 'int',
            'new_password' => 'required|confirmed|min:' . Config::get('password.min_length', 8),
            'dashboard' => 'int',
        ];
    }
}
