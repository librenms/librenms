<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;
use Silber\Bouncer\BouncerFacade as Bouncer;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if ($this->user()->can('create', User::class)) {
            if ($this->user()->cannot('manage', Bouncer::role())) {
                unset($this['roles']);
            }

            return true;
        }

        return false;
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
            'roles' => 'array',
            'roles.*' => Rule::in(Bouncer::role()->pluck('name')),
            'new_password' => 'required|confirmed|min:' . Config::get('password.min_length', 8),
            'dashboard' => 'int',
        ];
    }
}
