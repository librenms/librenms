<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var User|null $target_user */
        $target_user = $this->route('user');

        return $target_user && $this->user()->can('update', $target_user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $user = $this->route('user');

        if ($this->user()->can('update', User::class) && (! $user || ! $user->is($this->user()))) {
            return [
                'realname' => 'nullable|max:64|string',
                'email' => 'nullable|email|max:64',
                'descr' => 'nullable|max:30|string',
                'new_password' => ['nullable', 'confirmed', Password::defaults()],
                'new_password_confirmation' => 'nullable|same:new_password',
                'dashboard' => 'int',
                'roles' => [
                    'array',
                    Rule::when($this->user()->cannot('manage', Role::class), 'size:0'),
                ],
                'roles.*' => Rule::in(Role::query()->pluck('name')),
                'enabled' => 'boolean',
                'can_modify_passwd' => [
                    'boolean',
                    Rule::when($this->route('user')->is($this->user()), 'prohibited'),
                ],
            ];
        }

        return [
            'realname' => 'nullable|max:64|string',
            'email' => 'nullable|email|max:64',
            'descr' => 'nullable|max:30|string',
            'old_password' => 'nullable|string',
            'new_password' => ['nullable', 'confirmed', Password::defaults()],
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
        $validator->after(function ($validator): void {
            $user = $this->route('user');
            if ($user && $user->is($this->user())) {
                if ($this->input('new_password')) {
                    if ($this->input('old_password')) {
                        if (! Hash::check($this->old_password, $user->password)) {
                            $validator->errors()->add('old_password', __('Existing password did not match'));
                        }
                    } else {
                        $validator->errors()->add('old_password', __('The :attribute field is required.', ['attribute' => 'old_password']));
                    }
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $user = $this->route('user');

        // Only handle checkboxes for admins updating other users (where the checkboxes exist in the UI)
        if ($this->user()->can('update', User::class) && (! $user || ! $user->is($this->user()))) {
            $this->merge([
                'enabled' => $this->boolean('enabled'),
                'can_modify_passwd' => $this->boolean('can_modify_passwd'),
            ]);
        }
    }
}
