<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $this->user()->can('update', $role);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $rules = [
            'permissions' => 'array',
        ];

        // Do not allow renaming protected roles
        if (in_array(strtolower((string) $role->name), ['admin', 'global-read'])) {
            $rules['name'] = 'required|in:' . $role->name;
        } else {
            $rules['name'] = 'required|unique:roles,name,' . $role->id . '|regex:/^[a-z-]+$/';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => __('permissions.rbac.role_name_regex'),
        ];
    }
}
