<?php

namespace App\Http\Requests;

use App\Models\User;
use Hash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LibreNMS\Config;
use Silber\Bouncer\BouncerFacade as Bouncer;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->route('user');
        if ($user && $this->user()->can('update', $user)) {
            // normal users cannot update their roles or ability to modify a password
            if ($this->user()->cannot('manage', Bouncer::role())) {
                unset($this['roles']);
            }

            if ($user->is($this->user())) {
                unset($this['can_modify_passwd']);
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
        if ($this->user()->can('update', User::class)) {
            return [
                'realname' => 'nullable|max:64|alpha_space',
                'email' => 'nullable|email|max:64',
                'descr' => 'nullable|max:30|alpha_space',
                'new_password' => 'nullable|confirmed|min:' . Config::get('password.min_length', 8),
                'new_password_confirmation' => 'nullable|same:new_password',
                'dashboard' => 'int',
                'roles' => 'array',
                'roles.*' => Rule::in(Bouncer::role()->pluck('name')),
                'enabled' => 'nullable',
                'can_modify_passwd' => 'nullable',
            ];
        }

        return [
            'realname' => 'nullable|max:64|alpha_space',
            'email' => 'nullable|email|max:64',
            'descr' => 'nullable|max:30|alpha_space',
            'old_password' => 'nullable|string',
            'new_password' => 'nullable|confirmed|min:' . Config::get('password.min_length', 8),
            'new_password_confirmation' => 'nullable|same:new_password',
            'dashboard' => 'int',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // if not an admin and new_password is set, check old password matches
            $user = $this->route('user');
            if ($user && $this->user()->can('update', $user) && $this->user()->is($user)) {
                if ($this->get('new_password')) {
                    if ($this->get('old_password')) {
                        $user = $this->route('user');
                        if ($user && ! Hash::check($this->old_password, $user->password)) {
                            $validator->errors()->add('old_password', __('Existing password did not match'));
                        }
                    } else {
                        $validator->errors()->add('old_password', __('The :attribute field is required.', ['attribute' => 'old_password']));
                    }
                }
            }
        });
    }
}
