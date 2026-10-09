<?php

namespace App\Http\Requests;

use App\Models\Bill;

class StoreBillRequest extends UpdateBillRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Bill::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'port_id' => ['nullable', 'integer', 'exists:ports,port_id'],
        ]);
    }
}
