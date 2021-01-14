<?php

namespace App\Http\Requests;

use Hash;
use Illuminate\Foundation\Http\FormRequest;
use LibreNMS\Config;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->user()->isAdmin()) {
            return true;
        }

        $user = $this->route('user');
        if ($user && $this->user()->can('update', $user)) {
            // normal users cannot edit their level or ability to modify a password
            unset($this['level'], $this['can_modify_passwd']);

            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'realname' => 'nullable|max:64|alpha_space',
            'email' => 'nullable|email|max:64',
            'descr' => 'nullable|max:30|alpha_space',
            'level' => 'int',
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
            if (! $this->user()->isAdmin()) {
                if ($this->has('new_password')) {
                    if ($this->has('old_password')) {
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
