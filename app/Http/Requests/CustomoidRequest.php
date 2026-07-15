<?php

namespace App\Http\Requests;

use App\Models\Customoid;
use Illuminate\Foundation\Http\FormRequest;

class CustomoidRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $customoid = $this->route('customoid');
        if ($customoid instanceof Customoid) {
            return $this->user()->can('update', $customoid);
        }

        return $this->user()->can('create', Customoid::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:200',
            'oid' => 'required|string|max:255',
            'datatype' => 'required|string|in:COUNTER,GAUGE',
            'unit' => 'nullable|string|max:10',
            'divisor' => 'nullable|numeric',
            'multiplier' => 'nullable|numeric',
            'user_func' => 'nullable|string|max:50',
            'limit' => 'nullable|numeric',
            'limit_warn' => 'nullable|numeric',
            'limit_low' => 'nullable|numeric',
            'limit_low_warn' => 'nullable|numeric',
            'alerts' => 'nullable|in:on,off',
            'passed' => 'nullable|in:on,off',
        ];

        // device_id is required only when creating
        if ($this->isMethod('post') && ! $this->route('customoid')) {
            $rules['device_id'] = 'required|integer|exists:devices,device_id';
        }

        return $rules;
    }
}
