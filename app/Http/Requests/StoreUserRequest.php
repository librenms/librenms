<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use LibreNMS\Authentication\LegacyAuth;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'alpha_dash',
                'max:255',
                Rule::unique('users', 'username')->where('auth_type', LegacyAuth::getType()),
            ],
            'realname' => 'nullable|max:64|alpha_space',
            'email' => 'nullable|email|max:64',
            'descr' => 'nullable|max:30|alpha_space',
            'roles' => [
                'array',
                Rule::when($this->user()->cannot('update', Role::class), 'size:0'),
            ],
            'roles.*' => 'exists:roles,name',
            'new_password' => ['required', 'confirmed', Password::defaults()],
            'dashboard' => 'int',
        ];
    }
}
